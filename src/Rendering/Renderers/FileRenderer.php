<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class FileRenderer extends QuestionRenderer
{
    public function getDisplayValue(): ?array
    {
        if (! is_array($this->value)) {
            return null;
        }

        return array_map(function (array $file): array {
            $content = $file['content'] ?? null;
            $type = $file['type'] ?? '';
            $isImage = str_starts_with($type, 'image/');

            return [
                'name' => $file['name'] ?? '',
                'type' => $type,
                'isImage' => $isImage,
                'src' => ($isImage && $content) ? $this->resolveFileToBase64($content) : null,
            ];
        }, $this->value);
    }

    protected function getViewType(): string
    {
        return 'file';
    }

    protected function getExtraData(): array
    {
        return [
            'files' => $this->getDisplayValue() ?? [],
        ];
    }
}
