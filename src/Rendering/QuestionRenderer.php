<?php

namespace JibayMcs\SurveyJs\Rendering;

abstract class QuestionRenderer
{
    public function __construct(
        protected array $question,
        protected mixed $value,
        protected string $locale,
        protected array $allData = [],
        protected ?string $storageDisk = null,
        protected array $options = [],
    ) {}

    public function toArray(): array
    {
        return array_merge([
            'type' => $this->question['type'] ?? 'text',
            'name' => $this->question['name'] ?? '',
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'value' => $this->value,
            'displayValue' => $this->getDisplayValue(),
            'isAnswered' => $this->isAnswered(),
            'view' => $this->getViewName(),
            'showNumber' => $this->question['showNumber'] ?? true,
            'titleLocation' => $this->question['titleLocation'] ?? 'default',
        ], $this->getExtraData());
    }

    public function getTitle(): string
    {
        return $this->resolveLocale($this->question['title'] ?? $this->question['name'] ?? '') ?? '';
    }

    public function getDescription(): ?string
    {
        return $this->resolveLocale($this->question['description'] ?? null);
    }

    abstract public function getDisplayValue(): mixed;

    public function isAnswered(): bool
    {
        return $this->value !== null && $this->value !== '' && $this->value !== [];
    }

    public function getViewName(): string
    {
        return 'survey-js::rendering.types.' . $this->getViewType();
    }

    abstract protected function getViewType(): string;

    protected function getExtraData(): array
    {
        return [];
    }

    protected function resolveLocale(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value[$this->locale] ?? $value['default'] ?? reset($value) ?: null;
        }

        return (string) $value;
    }

    protected function resolveChoiceText(mixed $choice): string
    {
        if (is_string($choice) || is_numeric($choice)) {
            return (string) $choice;
        }

        if (is_array($choice)) {
            return $this->resolveLocale($choice['text'] ?? $choice['value'] ?? '') ?? '';
        }

        return '';
    }

    protected function resolveChoiceValue(mixed $choice): mixed
    {
        if (is_string($choice) || is_numeric($choice)) {
            return $choice;
        }

        if (is_array($choice)) {
            return $choice['value'] ?? '';
        }

        return '';
    }

    protected function resolveFileToBase64(string $path): ?string
    {
        // Already a data URI — return as-is
        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        // External URL — return as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (! $this->storageDisk) {
            return null;
        }

        $storage = \Illuminate\Support\Facades\Storage::disk($this->storageDisk);

        if (! $storage->exists($path)) {
            return null;
        }

        $mimeType = $storage->mimeType($path);
        $contents = $storage->get($path);

        return "data:{$mimeType};base64," . base64_encode($contents);
    }
}
