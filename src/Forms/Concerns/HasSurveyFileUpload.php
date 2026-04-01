<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Illuminate\Support\Facades\URL;

trait HasSurveyFileUpload
{
    protected bool $fileUploadEnabled = false;

    protected ?string $fileUploadDisk = null;

    protected ?string $fileUploadDirectory = null;

    protected ?string $fileUploadVisibility = null;

    protected ?int $fileUploadMaxSize = null;

    protected ?array $fileUploadAcceptedTypes = null;

    public function fileUpload(bool $condition = true): static
    {
        $this->fileUploadEnabled = $condition;

        return $this;
    }

    public function fileUploadDisk(?string $disk): static
    {
        $this->fileUploadDisk = $disk;

        return $this;
    }

    public function fileUploadDirectory(?string $directory): static
    {
        $this->fileUploadDirectory = $directory;

        return $this;
    }

    public function fileUploadVisibility(?string $visibility): static
    {
        $this->fileUploadVisibility = $visibility;

        return $this;
    }

    public function fileUploadMaxSize(?int $bytes): static
    {
        $this->fileUploadMaxSize = $bytes;

        return $this;
    }

    public function fileUploadAcceptedTypes(?array $types): static
    {
        $this->fileUploadAcceptedTypes = $types;

        return $this;
    }

    public function getFileUploadDisk(): string
    {
        return $this->fileUploadDisk ?? config('survey-js.file_upload.disk', config('filesystems.default'));
    }

    public function getFileUploadDirectory(): string
    {
        return $this->fileUploadDirectory ?? config('survey-js.file_upload.directory', 'survey-uploads');
    }

    public function getFileUploadVisibility(): string
    {
        return $this->fileUploadVisibility ?? config('survey-js.file_upload.visibility', 'private');
    }

    public function getFileUploadMaxSize(): ?int
    {
        return $this->fileUploadMaxSize ?? config('survey-js.file_upload.max_size');
    }

    public function getFileUploadAcceptedTypes(): ?array
    {
        return $this->fileUploadAcceptedTypes ?? config('survey-js.file_upload.accepted_types');
    }

    public function getFileUploadUrl(): ?string
    {
        if (! $this->fileUploadEnabled) {
            return null;
        }

        return URL::signedRoute('survey-js.upload', [
            'disk' => $this->getFileUploadDisk(),
            'directory' => $this->getFileUploadDirectory(),
            'visibility' => $this->getFileUploadVisibility(),
            'maxSize' => $this->getFileUploadMaxSize(),
            'acceptedTypes' => $this->getFileUploadAcceptedTypes()
                ? implode(',', $this->getFileUploadAcceptedTypes())
                : null,
        ]);
    }

    public function getFileDownloadUrl(): ?string
    {
        if (! $this->fileUploadEnabled) {
            return null;
        }

        $disk = $this->getFileUploadDisk();
        $directory = $this->getFileUploadDirectory();
        $token = hash_hmac('sha256', "download:{$disk}:{$directory}", config('app.key'));

        return route('survey-js.download').'?'.http_build_query([
            'disk' => $disk,
            'directory' => $directory,
            'token' => $token,
        ]);
    }

    public function getFileDeleteUrl(): ?string
    {
        if (! $this->fileUploadEnabled) {
            return null;
        }

        $disk = $this->getFileUploadDisk();
        $directory = $this->getFileUploadDirectory();
        $token = hash_hmac('sha256', "delete:{$disk}:{$directory}", config('app.key'));

        return route('survey-js.delete').'?'.http_build_query([
            'disk' => $disk,
            'directory' => $directory,
            'token' => $token,
        ]);
    }
}
