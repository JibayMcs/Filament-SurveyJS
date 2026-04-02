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
        $questionType = $request->input('questionType');

        $rules = ['files' => 'required|array', 'files.*' => 'file'];

        if ($maxSize) {
            $maxKb = (int) ceil($maxSize / 1024);
            $rules['files.*'] .= "|max:{$maxKb}";
        }

        // Skip mimes validation for signaturepad (always produces PNG/JPEG)
        if ($acceptedTypes && $questionType !== 'signaturepad') {
            $validationRules = $this->buildFileTypeRules($acceptedTypes);
            if ($validationRules) {
                $rules['files.*'] .= '|'.$validationRules;
            }
        }

        $typeMessage = __('survey-js::survey-js.validation.file_type_not_allowed', [
            'types' => $acceptedTypes ?? '',
        ]);

        $messages = [
            'files.required' => __('survey-js::survey-js.validation.files_required'),
            'files.*.file' => __('survey-js::survey-js.validation.file_invalid'),
            'files.*.max' => __('survey-js::survey-js.validation.file_too_large', [
                'max' => $maxSize ? round($maxSize / 1024 / 1024, 1).'MB' : '',
            ]),
            'files.*.mimes' => $typeMessage,
            'files.*.mimetypes' => $typeMessage,
        ];

        $request->validate($rules, $messages);

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

    /**
     * Build Laravel validation rules from accepted types like ".pdf, image/*, .docx".
     *
     * - ".pdf", ".docx" → mimes:pdf,docx
     * - "image/*", "video/*" → mimetypes:image/*,video/* (wildcard MIME matching)
     * - "application/pdf" → mimetypes:application/pdf (exact MIME)
     */
    protected function buildFileTypeRules(string $acceptedTypes): string
    {
        $types = array_map('trim', explode(',', $acceptedTypes));
        $mimes = [];
        $mimetypes = [];

        foreach ($types as $type) {
            // .pdf, .docx → extension-based
            if (str_starts_with($type, '.')) {
                $mimes[] = ltrim($type, '.');
            }
            // image/*, video/*, audio/* → wildcard MIME
            elseif (str_contains($type, '/*')) {
                $category = explode('/', $type)[0];
                $mimetypes[] = "{$category}/*";
            }
            // application/pdf → exact MIME
            elseif (str_contains($type, '/')) {
                $mimetypes[] = $type;
            }
        }

        $rules = [];

        if ($mimes) {
            $rules[] = 'mimes:'.implode(',', $mimes);
        }

        if ($mimetypes) {
            $rules[] = 'mimetypes:'.implode(',', $mimetypes);
        }

        // When both mimes and mimetypes are present, the file must pass
        // at least one of them. We combine them so either rule can match.
        if ($mimes && $mimetypes) {
            // Use mimetypes only — convert extensions to their MIME equivalents
            // so we have a single rule that accepts everything
            $allMimetypes = $mimetypes;
            foreach ($mimes as $ext) {
                $mime = $this->extToMimetype($ext);
                if ($mime) {
                    $allMimetypes[] = $mime;
                }
            }

            return 'mimetypes:'.implode(',', array_unique($allMimetypes));
        }

        return implode('|', $rules);
    }

    protected function extToMimetype(string $ext): ?string
    {
        $map = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
        ];

        return $map[$ext] ?? null;
    }
}
