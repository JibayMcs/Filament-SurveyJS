<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use Carbon\Carbon;
use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class TextRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        $type = $this->question['type'] ?? 'text';

        return $type === 'multipletext' ? 'multipletext' : 'text';
    }

    public function getDisplayValue(): mixed
    {
        $type = $this->question['type'] ?? 'text';

        if ($type === 'multipletext') {
            return $this->getMultipleTextItems();
        }

        if ($this->value === null || $this->value === '') {
            return $this->value;
        }

        $inputType = $this->question['inputType'] ?? 'text';

        return $this->formatByInputType($this->value, $inputType);
    }

    protected function getExtraData(): array
    {
        $type = $this->question['type'] ?? 'text';

        if ($type === 'multipletext') {
            return ['items' => $this->getDisplayValue()];
        }

        return [];
    }

    protected function getMultipleTextItems(): array
    {
        $items = [];

        foreach ($this->question['items'] ?? [] as $item) {
            $name = $item['name'] ?? '';
            $title = $this->resolveLocale($item['title'] ?? $name);
            $value = is_array($this->value) ? ($this->value[$name] ?? null) : null;
            $inputType = $item['inputType'] ?? 'text';

            $items[] = [
                'title' => $title,
                'value' => $value ? $this->formatByInputType($value, $inputType) : $value,
            ];
        }

        return $items;
    }

    protected function formatByInputType(mixed $value, string $inputType): mixed
    {
        $formatMap = [
            'date' => 'dateFormat',
            'datetime-local' => 'datetimeFormat',
            'time' => 'timeFormat',
        ];

        $optionKey = $formatMap[$inputType] ?? null;

        if (! $optionKey || ! is_string($value)) {
            return $value;
        }

        $format = $this->options[$optionKey] ?? null;

        if (! $format) {
            return $value;
        }

        try {
            $carbon = Carbon::parse($value)->locale($this->locale);

            return $carbon->isoFormat($format);
        } catch (\Throwable) {
            return $value;
        }
    }
}
