<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class HtmlRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        return 'html';
    }

    public function getDisplayValue(): mixed
    {
        if ($this->isExpressionType()) {
            return $this->value;
        }

        return $this->resolveLocale($this->question['html'] ?? null);
    }

    public function isAnswered(): bool
    {
        return true;
    }

    protected function isExpressionType(): bool
    {
        return ($this->question['type'] ?? 'html') === 'expression';
    }
}
