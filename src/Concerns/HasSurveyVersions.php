<?php

namespace JibayMcs\SurveyJs\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use JibayMcs\SurveyJs\Models\SurveyJsVersion;

trait HasSurveyVersions
{
    public function surveyVersions(): MorphMany
    {
        return $this->morphMany(SurveyJsVersion::class, 'versionable');
    }

    public function surveyVersionsFor(string $fieldName): MorphMany
    {
        return $this->surveyVersions()->where('field_name', $fieldName);
    }

    public function latestSurveyVersion(string $fieldName): ?SurveyJsVersion
    {
        return $this->surveyVersionsFor($fieldName)->latest('created_at')->first();
    }

    public function completedSurveyVersions(string $fieldName): Collection
    {
        return $this->surveyVersionsFor($fieldName)->where('completed', true)->latest('created_at')->get();
    }
}
