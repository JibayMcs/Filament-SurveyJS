<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class PanelRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        return match ($this->question['type'] ?? 'panel') {
            'paneldynamic' => 'paneldynamic',
            default => 'panel',
        };
    }

    public function getDisplayValue(): mixed
    {
        return null;
    }

    public function isAnswered(): bool
    {
        return true;
    }

    protected function getExtraData(): array
    {
        return [
            'panelTitle' => $this->resolveLocale($this->question['title'] ?? null),
        ];
    }
}
