<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class BooleanRenderer extends QuestionRenderer
{
    public function getDisplayValue(): ?string
    {
        if ($this->value === null) {
            return null;
        }

        if ($this->value) {
            return $this->resolveLocale($this->question['labelTrue'] ?? null) ?? __('Yes');
        }

        return $this->resolveLocale($this->question['labelFalse'] ?? null) ?? __('No');
    }

    protected function getViewType(): string
    {
        return 'boolean';
    }

    protected function getExtraData(): array
    {
        return [
            'boolValue' => (bool) $this->value,
        ];
    }
}
