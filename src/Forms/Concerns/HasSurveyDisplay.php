<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Closure;
use JibayMcs\SurveyJs\Enums\CheckErrorsMode;
use JibayMcs\SurveyJs\Enums\ProgressBarLocation;

trait HasSurveyDisplay
{
    public ?bool $panelless = false;

    public ?bool $transparent = false;

    public ?array $themeJson = null;

    protected ?bool $showProgressBar = false;

    public ?bool $progressBarPercent = false;

    protected string|array|null $progressBarColor = null;

    protected ?ProgressBarLocation $progressBarLocation = null;

    protected ?bool $autoAdvanceEnabled = false;

    protected ?bool $autoAdvanceAllowComplete = false;

    protected ?CheckErrorsMode $checkErrorsMode = null;

    public ?bool $readOnly = false;

    public ?bool $contained = true;

    public ?bool $containedWithTitle = true;

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

    public function showProgressBar(?bool $condition = true, bool $hasPercent = false, string|array $color = 'primary'): static
    {
        $this->showProgressBar = $condition;
        $this->progressBarPercent = $condition ? $hasPercent : false;
        $this->progressBarColor = $condition ? $color : null;

        return $this;
    }

    public function getProgressBarColorHex(): ?string
    {
        return $this->resolveColorHex($this->progressBarColor);
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

    public function isReadOnly(): bool
    {
        return $this->readOnly || $this->isDisabled();
    }

    public function contained(?bool $condition = true, bool $withTitle = true): static
    {
        $this->contained = $condition;
        $this->containedWithTitle = $condition ? $withTitle : false;

        return $this;
    }

    public function theme(Closure|string|array|null $theme): static
    {
        if (is_callable($theme)) {
            $this->themeJson = $this->evaluate($theme);
        } elseif (is_string($theme)) {
            $this->themeJson = json_decode($theme, true);
        } else {
            $this->themeJson = $theme;
        }

        return $this;
    }

    public function getThemeJson(): ?array
    {
        return $this->themeJson;
    }
}
