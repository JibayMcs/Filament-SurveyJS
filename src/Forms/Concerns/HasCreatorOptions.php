<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Closure;
use JibayMcs\SurveyJs\Enums\QuestionType;

trait HasCreatorOptions
{
    protected ?string $locale = null;

    protected ?bool $showDesignerTab = null;

    protected ?bool $showPreviewTab = null;

    protected ?bool $showJsonEditorTab = null;

    protected ?bool $showLogicTab = null;

    protected ?bool $showTranslationTab = null;

    protected ?bool $showThemeTab = null;

    protected ?array $questionTypes = null;

    protected ?string $pageEditMode = null;

    protected ?bool $autoSaveEnabled = null;

    protected ?int $autoSaveDelay = null;

    protected ?bool $readOnly = null;

    protected ?Closure $onModifiedCallback = null;

    protected ?Closure $onSaveCallback = null;

    public function locale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale ?? config('survey-js.locale') ?? app()->getLocale();
    }

    public function showDesignerTab(?bool $condition = true): static
    {
        $this->showDesignerTab = $condition;

        return $this;
    }

    public function showPreviewTab(?bool $condition = true): static
    {
        $this->showPreviewTab = $condition;

        return $this;
    }

    public function showJsonEditorTab(?bool $condition = true): static
    {
        $this->showJsonEditorTab = $condition;

        return $this;
    }

    public function showLogicTab(?bool $condition = true): static
    {
        $this->showLogicTab = $condition;

        return $this;
    }

    public function showTranslationTab(?bool $condition = true): static
    {
        $this->showTranslationTab = $condition;

        return $this;
    }

    public function showThemeTab(?bool $condition = true): static
    {
        $this->showThemeTab = $condition;

        return $this;
    }

    /**
     * @param  array<QuestionType|string>  $types
     */
    public function questionTypes(?array $types): static
    {
        $this->questionTypes = $types !== null
            ? array_map(fn ($type) => $type instanceof QuestionType ? $type->value : $type, $types)
            : null;

        return $this;
    }

    public function pageEditMode(?string $mode): static
    {
        $this->pageEditMode = $mode;

        return $this;
    }

    public function autoSave(?bool $condition = true, ?int $delay = null): static
    {
        $this->autoSaveEnabled = $condition;

        if ($delay !== null) {
            $this->autoSaveDelay = $delay;
        }

        return $this;
    }

    public function readOnly(?bool $condition = true): static
    {
        $this->readOnly = $condition;

        return $this;
    }

    public function onModified(?Closure $callback): static
    {
        $this->onModifiedCallback = $callback;

        return $this;
    }

    public function onSave(?Closure $callback): static
    {
        $this->onSaveCallback = $callback;

        return $this;
    }

    public function getCreatorOptions(): array
    {
        return array_filter([
            'showDesignerTab' => $this->showDesignerTab,
            'showPreviewTab' => $this->showPreviewTab,
            'showJSONEditorTab' => $this->showJsonEditorTab,
            'showLogicTab' => $this->showLogicTab,
            'showTranslationTab' => $this->showTranslationTab,
            'showThemeTab' => $this->showThemeTab,
            'questionTypes' => $this->questionTypes,
            'pageEditMode' => $this->pageEditMode,
            'autoSaveEnabled' => $this->autoSaveEnabled,
            'autoSaveDelay' => $this->autoSaveDelay,
            'readOnly' => $this->readOnly,
        ], fn ($value) => $value !== null);
    }
}
