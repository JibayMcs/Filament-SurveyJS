<?php

namespace JibayMcs\SurveyJs\Forms;

use Filament\Forms\Components\Field;
use JibayMcs\SurveyJs\Forms\Concerns\HasCreatorOptions;
use JibayMcs\SurveyJs\Models\SurveyJsVersion;

class SurveyJSCreatorField extends Field
{
    use HasCreatorOptions;

    protected string $view = 'survey-js::components.surveyjs-creator-field';

    public static function isAvailable(): bool
    {
        return file_exists(__DIR__ . '/../../resources/dist/survey-js-creator.js');
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::isAvailable()) {
            throw new \RuntimeException(
                'SurveyJS Creator assets are not compiled. '
                . 'Install the commercial dependencies first: '
                . 'cd vendor/jibaymcs/survey-js && npm install survey-creator-core survey-creator-js && npm run build'
            );
        }

        $this->afterStateHydrated(function (SurveyJSCreatorField $component, $state): void {
            if ($state === null) {
                $component->state([]);
            } elseif (is_string($state)) {
                $component->state(json_decode($state, true) ?? []);
            }
        });

        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                return $state;
            }

            return json_decode($state, true) ?? [];
        });

        $this->afterStateUpdated(function ($state, SurveyJSCreatorField $component): void {
            $json = is_string($state) ? json_decode($state, true) : $state;

            if ($component->onSaveCallback !== null) {
                $component->evaluate($component->onSaveCallback, [
                    'json' => $json,
                    'record' => $component->getRecord(),
                ]);
            }

            if ($component->onModifiedCallback !== null) {
                $component->evaluate($component->onModifiedCallback, [
                    'json' => $json,
                    'record' => $component->getRecord(),
                ]);
            }

            $record = $component->getRecord();

            if ($component->versioningEnabled && $record) {
                SurveyJsVersion::create([
                    'versionable_type' => $record->getMorphClass(),
                    'versionable_id' => $record->getKey(),
                    'field_name' => $component->getStatePath(),
                    'data' => $json,
                    'completed' => false,
                    'created_at' => now(),
                ]);
            }
        });
    }
}
