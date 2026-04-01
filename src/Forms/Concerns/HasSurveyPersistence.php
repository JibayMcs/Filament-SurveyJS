<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

trait HasSurveyPersistence
{
    protected ?bool $autoSaveEnabled = null;

    protected ?bool $versioningEnabled = null;

    protected bool $versionOnEveryChange = false;

    public function autoSave(?bool $condition = true, int $debounce = 500): static
    {
        $this->autoSaveEnabled = $condition;

        if ($condition) {
            $this->live(debounce: $debounce);
        }

        return $this;
    }

    public function versioning(?bool $condition = true, bool $onEveryChange = false, int $debounce = 500): static
    {
        $this->versioningEnabled = $condition;
        $this->versionOnEveryChange = $onEveryChange;

        if ($onEveryChange) {
            $this->live(debounce: $debounce);
        }

        return $this;
    }
}
