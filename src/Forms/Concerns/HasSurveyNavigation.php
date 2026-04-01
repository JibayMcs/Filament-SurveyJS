<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

trait HasSurveyNavigation
{
    public ?bool $showNavigationButtons = null;

    public ?bool $showPrevButton = null;

    public ?string $pageNextText = null;

    public ?string $pagePrevText = null;

    public ?string $completeText = null;

    public string $prevButtonColor = 'gray';

    public string $nextButtonColor = 'primary';

    public string $completeButtonColor = 'success';

    public function showNavigationButtons(?bool $condition = true, bool $autoAdvance = true): static
    {
        $this->showNavigationButtons = $condition;

        if (! $condition && $autoAdvance) {
            $this->autoAdvanceEnabled = true;
            $this->autoAdvanceAllowComplete = true;
        }

        return $this;
    }

    public function showPrevButton(?bool $condition = true): static
    {
        $this->showPrevButton = $condition;

        return $this;
    }

    public function pageNextText(string $text): static
    {
        $this->pageNextText = $text;

        return $this;
    }

    public function pagePrevText(string $text): static
    {
        $this->pagePrevText = $text;

        return $this;
    }

    public function completeText(string $text): static
    {
        $this->completeText = $text;

        return $this;
    }

    public function prevButtonColor(string $color): static
    {
        $this->prevButtonColor = $color;

        return $this;
    }

    public function nextButtonColor(string $color): static
    {
        $this->nextButtonColor = $color;

        return $this;
    }

    public function completeButtonColor(string $color): static
    {
        $this->completeButtonColor = $color;

        return $this;
    }
}
