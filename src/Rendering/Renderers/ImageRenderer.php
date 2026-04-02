<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class ImageRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        return match ($this->question['type'] ?? 'image') {
            'imagepicker' => 'imagepicker',
            default => 'image',
        };
    }

    public function getDisplayValue(): mixed
    {
        if ($this->isImageType()) {
            return null;
        }

        $selected = is_array($this->value) ? $this->value : [$this->value];
        $choices = $this->question['choices'] ?? [];

        return array_values(array_filter(array_map(function (mixed $choice) use ($selected): ?string {
            $choiceValue = $this->resolveChoiceValue($choice);

            if (in_array($choiceValue, $selected, false)) {
                return $this->resolveChoiceText($choice);
            }

            return null;
        }, $choices)));
    }

    public function isAnswered(): bool
    {
        if ($this->isImageType()) {
            return false;
        }

        return parent::isAnswered();
    }

    protected function getExtraData(): array
    {
        if ($this->isImageType()) {
            return [
                'imageLink' => $this->question['imageLink'] ?? null,
            ];
        }

        $selected = is_array($this->value) ? $this->value : [$this->value];
        $choices = $this->question['choices'] ?? [];

        $images = array_map(function (mixed $choice) use ($selected): array {
            $choiceValue = $this->resolveChoiceValue($choice);

            return [
                'value' => $choiceValue,
                'text' => $this->resolveChoiceText($choice),
                'imageLink' => is_array($choice) ? ($choice['imageLink'] ?? null) : null,
                'selected' => in_array($choiceValue, $selected, false),
            ];
        }, $choices);

        return [
            'images' => array_values($images),
        ];
    }

    protected function isImageType(): bool
    {
        return ($this->question['type'] ?? 'image') === 'image';
    }
}
