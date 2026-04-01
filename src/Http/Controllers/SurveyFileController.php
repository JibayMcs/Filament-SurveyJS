<?php

namespace JibayMcs\SurveyJs\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyFileController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid signature.');
        }

        $disk = $request->query('disk');
        $directory = $request->query('directory');
        $visibility = $request->query('visibility', 'private');
        $maxSize = $request->query('maxSize');
        $acceptedTypes = $request->query('acceptedTypes');

        $rules = ['files' => 'required|array', 'files.*' => 'file'];

        if ($maxSize) {
            $maxKb = (int) ceil($maxSize / 1024);
            $rules['files.*'] .= "|max:{$maxKb}";
        }

        if ($acceptedTypes) {
            $mimes = $this->acceptedTypesToMimes($acceptedTypes);
            if ($mimes) {
                $rules['files.*'] .= "|mimes:{$mimes}";
            }
        }

        $request->validate($rules);

        $storage = Storage::disk($disk);
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $hash = Str::random(40);
            $extension = $file->getClientOriginalExtension();
            $filename = "{$hash}.{$extension}";
            $path = $storage->putFileAs($directory, $file, $filename, $visibility);

            $uploaded[] = [
                'file' => [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                ],
                'content' => $path,
            ];
        }

        return response()->json(['files' => $uploaded]);
    }

    public function download(Request $request): StreamedResponse
    {
        $disk = $request->query('disk');
        $directory = $request->query('directory');
        $token = $request->query('token');
        $path = $request->query('path');

        $expected = hash_hmac('sha256', "download:{$disk}:{$directory}", config('app.key'));

        if (! hash_equals($expected, $token ?? '')) {
            abort(403, 'Invalid token.');
        }

        if (! $path || ! str_starts_with($path, $directory.'/')) {
            abort(403, 'Invalid file path.');
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            abort(404, 'File not found.');
        }

        $mimeType = $storage->mimeType($path);
        $filename = basename($path);

        return $storage->response($path, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $disk = $request->query('disk');
        $directory = $request->query('directory');
        $token = $request->query('token');
        $path = $request->input('path');

        $expected = hash_hmac('sha256', "delete:{$disk}:{$directory}", config('app.key'));

        if (! hash_equals($expected, $token ?? '')) {
            abort(403, 'Invalid token.');
        }

        if (! $path || ! str_starts_with($path, $directory.'/')) {
            abort(403, 'Invalid file path.');
        }

        $storage = Storage::disk($disk);

        if ($storage->exists($path)) {
            $storage->delete($path);
        }

        return response()->json(['status' => 'success']);
    }

    protected function acceptedTypesToMimes(string $acceptedTypes): string
    {
        $types = array_map('trim', explode(',', $acceptedTypes));
        $mimes = [];

        foreach ($types as $type) {
            // .pdf, .docx → pdf, docx
            if (str_starts_with($type, '.')) {
                $mimes[] = ltrim($type, '.');
            }
            // image/* → skip (let Laravel handle it)
            // application/pdf → pdf
            elseif (str_contains($type, '/') && ! str_contains($type, '*')) {
                $ext = last(explode('/', $type));
                $mimes[] = $ext;
            }
        }

        return implode(',', $mimes);
    }
}
