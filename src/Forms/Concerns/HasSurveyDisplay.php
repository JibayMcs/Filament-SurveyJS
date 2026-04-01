<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use JibayMcs\SurveyJs\Enums\CheckErrorsMode;
use JibayMcs\SurveyJs\Enums\ProgressBarLocation;

trait HasSurveyDisplay
{
    public ?bool $panelless = false;

    public ?bool $transparent = false;

    protected ?bool $showProgressBar = false;

    public ?bool $progressBarPercent = false;

    protected ?ProgressBarLocation $progressBarLocation = null;

    protected ?bool $autoAdvanceEnabled = false;

    protected ?bool $autoAdvanceAllowComplete = false;

    protected ?CheckErrorsMode $checkErrorsMode = null;

    public ?bool $readOnly = false;

    public ?bool $contained = true;

    public function panelless(?bool $condition = true): static
    {
        $this->panelless = $condition;

        return $this;
    }

    public function transparent(?bool $condition = true): static
    {
        $this->transparent = $condition;

        return $this;
    }

    public function showProgressBar(?bool $condition = true, bool $hasPercent = false): static
    {
        $this->showProgressBar = $condition;
        $this->progressBarPercent = $condition ? $hasPercent : false;

        return $this;
    }

    public function progressBarLocation(ProgressBarLocation $location): static
    {
        $this->progressBarLocation = $location;

        return $this;
    }

    public function autoAdvance(?bool $condition = true): static
    {
        $this->autoAdvanceEnabled = $condition;
        $this->autoAdvanceAllowComplete = $condition;

        return $this;
    }

    public function checkErrorsMode(CheckErrorsMode $mode): static
    {
        $this->checkErrorsMode = $mode;

        return $this;
    }

    public function readOnly(?bool $condition = true): static
    {
        $this->readOnly = $condition;

        return $this;
    }

    public function contained(?bool $condition = true): static
    {
        $this->contained = $condition;

        return $this;
    }
}
