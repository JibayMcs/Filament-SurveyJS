<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class SliderRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        return 'slider';
    }

    public function getDisplayValue(): mixed
    {
        return $this->value;
    }

    protected function getExtraData(): array
    {
        return [
            'min' => $this->question['min'] ?? $this->question['rangeMin'] ?? 0,
            'max' => $this->question['max'] ?? $this->question['rangeMax'] ?? 100,
            'step' => $this->question['step'] ?? 1,
        ];
    }
}
