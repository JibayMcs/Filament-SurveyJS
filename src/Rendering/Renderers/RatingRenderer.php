<?php

namespace JibayMcs\SurveyJs\Rendering\Renderers;

use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class RatingRenderer extends QuestionRenderer
{
    protected function getViewType(): string
    {
        return 'rating';
    }

    public function getDisplayValue(): mixed
    {
        $max = $this->getRateMax();

        return $this->value !== null && $this->value !== ''
            ? "{$this->value} / {$max}"
            : null;
    }

    protected function getExtraData(): array
    {
        $rateMin = $this->getRateMin();
        $rateMax = $this->getRateMax();
        $stars = [];

        for ($i = $rateMin; $i <= $rateMax; $i++) {
            $stars[] = [
                'value' => $i,
                'filled' => $this->value !== null && $this->value !== '' && $i <= (int) $this->value,
            ];
        }

        return [
            'rateMin' => $rateMin,
            'rateMax' => $rateMax,
            'stars' => $stars,
        ];
    }

    protected function getRateMin(): int
    {
        return (int) ($this->question['rateMin'] ?? 1);
    }

    protected function getRateMax(): int
    {
        return (int) ($this->question['rateMax'] ?? $this->question['rateCount'] ?? 5);
    }
}
