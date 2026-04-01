<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-load
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref(id: 'survey-js-styles', package: 'jibaymcs/survey-js'))]"
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc(id: 'survey-js-form', package: 'jibaymcs/survey-js') }}"
        x-data="surveyjsForm({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            surveyJson: @js($field->getSurveyJson()),
            panelless: @js($field->panelless),
            transparent: @js($field->transparent),
            statePath: @js($getStatePath()),
            readOnly: @js($field->isReadOnly()),
            locale: @js($field->getLocale()),
            progressBarPercent: @js($field->progressBarPercent ?? false),
            contained: @js($field->contained ?? false),
            containedWithTitle: @js($field->containedWithTitle ?? true),
            fileUploadUrl: @js($field->getFileUploadUrl()),
            fileDownloadUrl: @js($field->getFileDownloadUrl()),
            fileDeleteUrl: @js($field->getFileDeleteUrl()),
            fileErrors: @js([
                'tooLarge' => __('survey-js::survey-js.upload.file_too_large'),
                'invalidType' => __('survey-js::survey-js.upload.invalid_type'),
                'failed' => __('survey-js::survey-js.upload.failed'),
            ]),
        })"
        wire:ignore
    >
        <template x-if="loading">
            <div class="flex justify-center items-center flex-col py-4">
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>
        </template>

        @if($field->contained)
            <fieldset class="fi-fieldset" x-show="!loading" x-cloak>
                @if($field->containedWithTitle)
                    <legend x-show="surveyTitle" x-text="surveyTitle"></legend>
                @endif
                <div x-ref="surveyContainer"></div>
            </fieldset>
        @else
            <div
                x-ref="surveyContainer"
                x-show="!loading"
                x-cloak
            ></div>
        @endif

        <template x-if="!loading">
            <div class="sjs-navigation">
                {{-- Gauche : Précédent --}}
                <div>
                    <x-filament::button
                        outlined
                        :color="$field->prevButtonColor"
                        x-show="!isFirstPage"
                        x-on:click="prevPage()"
                    >
                        {{ $field->pagePrevText ?? __('Précédent') }}
                    </x-filament::button>
                </div>

                {{-- Droite : Suivant / Terminer --}}
                <div class="sjs-navigation__right">
                    <x-filament::button
                        :color="$field->nextButtonColor"
                        x-show="!isLastPage"
                        x-on:click="nextPage()"
                    >
                        {{ $field->pageNextText ?? __('Suivant') }}
                    </x-filament::button>

                    <x-filament::button
                        :color="$field->completeButtonColor"
                        x-show="isLastPage && !readOnly"
                        x-on:click="completeSurvey()"
                    >
                        {{ $field->completeText ?? __('Terminer') }}
                    </x-filament::button>
                </div>
            </div>
        </template>
    </div>
</x-dynamic-component>
