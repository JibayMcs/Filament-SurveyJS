<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class ChoiceRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        $type = $this->question['type'] ?? 'checkbox';

        return match ($type) {
            'dropdown', 'tagbox' => 'choice-inline',
            'ranking' => 'ranking',
            default => 'choice',
        };
    }

    public function getDisplayValue(): mixed
    {
        $selectedValues = $this->getSelectedValues();
        $choices = $this->question['choices'] ?? [];
        $texts = [];

        foreach ($choices as $choice) {
            $choiceValue = $this->resolveChoiceValue($choice);

            if (in_array($choiceValue, $selectedValues, false)) {
                $texts[] = $this->resolveChoiceText($choice);
            }
        }

        // Handle "other" option
        if ($this->hasOtherSelected($selectedValues)) {
            $comment = $this->allData[($this->question['name'] ?? '') . '-Comment'] ?? null;
            $texts[] = $comment ?? __('Other');
        }

        return $texts;
    }

    public function getSelectedValues(): array
    {
        if ($this->value === null || $this->value === '' || $this->value === []) {
            return [];
        }

        return is_array($this->value) ? $this->value : [$this->value];
    }

    protected function getExtraData(): array
    {
        $type = $this->question['type'] ?? 'checkbox';
        $selectedValues = $this->getSelectedValues();
        $choices = $this->question['choices'] ?? [];
        $isMultiple = in_array($type, ['checkbox', 'tagbox', 'ranking']);

        return match ($type) {
            'dropdown', 'tagbox' => [
                'choices' => $this->buildSelectedOnly($choices, $selectedValues),
                'isMultiple' => $isMultiple,
            ],
            'ranking' => [
                'choices' => $this->buildRankedChoices($choices, $selectedValues),
                'isMultiple' => $isMultiple,
            ],
            default => [
                'choices' => $this->buildAllChoices($choices, $selectedValues),
                'isMultiple' => $isMultiple,
            ],
        };
    }

    protected function buildAllChoices(array $choices, array $selectedValues): array
    {
        $result = [];

        foreach ($choices as $choice) {
            $value = $this->resolveChoiceValue($choice);
            $text = $this->resolveChoiceText($choice);

            $result[] = [
                'value' => $value,
                'text' => $text,
                'selected' => in_array($value, $selectedValues, false),
            ];
        }

        // Handle "hasOther" option
        if ($this->hasOtherSelected($selectedValues)) {
            $comment = $this->allData[($this->question['name'] ?? '') . '-Comment'] ?? null;

            $result[] = [
                'value' => 'other',
                'text' => $comment ?? __('Other'),
                'selected' => true,
            ];
        }

        return $result;
    }

    protected function buildSelectedOnly(array $choices, array $selectedValues): array
    {
        $result = [];

        foreach ($choices as $choice) {
            $value = $this->resolveChoiceValue($choice);
            $text = $this->resolveChoiceText($choice);

            if (in_array($value, $selectedValues, false)) {
                $result[] = [
                    'value' => $value,
                    'text' => $text,
                    'selected' => true,
                ];
            }
        }

        // Handle "hasOther" option
        if ($this->hasOtherSelected($selectedValues)) {
            $comment = $this->allData[($this->question['name'] ?? '') . '-Comment'] ?? null;

            $result[] = [
                'value' => 'other',
                'text' => $comment ?? __('Other'),
                'selected' => true,
            ];
        }

        return $result;
    }

    protected function buildRankedChoices(array $choices, array $selectedValues): array
    {
        $result = [];
        $choiceMap = [];

        foreach ($choices as $choice) {
            $value = $this->resolveChoiceValue($choice);
            $text = $this->resolveChoiceText($choice);
            $choiceMap[$value] = $text;
        }

        // Order by the response order
        foreach ($selectedValues as $selectedValue) {
            if (isset($choiceMap[$selectedValue])) {
                $result[] = [
                    'value' => $selectedValue,
                    'text' => $choiceMap[$selectedValue],
                    'selected' => true,
                ];
            }
        }

        // Append unranked choices
        foreach ($choices as $choice) {
            $value = $this->resolveChoiceValue($choice);

            if (! in_array($value, $selectedValues, false)) {
                $result[] = [
                    'value' => $value,
                    'text' => $this->resolveChoiceText($choice),
                    'selected' => false,
                ];
            }
        }

        return $result;
    }

    protected function hasOtherSelected(array $selectedValues): bool
    {
        if (empty($this->question['hasOther'])) {
            return false;
        }

        return in_array('other', $selectedValues, true);
    }
}
