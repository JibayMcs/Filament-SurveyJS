<?php

namespace JibayMcs\SurveyJs\Rendering;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class SurveyRenderer
{
    protected string $locale;

    protected string $theme = 'filament';

    protected bool $showUnanswered = true;

    protected bool $showPageTitles = true;

    protected bool $showQuestionNumbers = true;

    protected bool $showPageBreaks = true;

    protected bool $showHeader = true;

    protected bool $showFooter = false;

    protected ?string $headerView = null;

    protected ?string $footerView = null;

    protected string $unansweredText = '—';

    protected array $viewData = [];

    protected ?string $storageDisk = null;

    protected string $dateFormat = 'L';

    protected string $datetimeFormat = 'L LT';

    protected string $timeFormat = 'LT';

    protected static array $customRenderers = [];

    protected static array $defaultRenderers = [
        'text' => Renderers\TextRenderer::class,
        'comment' => Renderers\TextRenderer::class,
        'multipletext' => Renderers\TextRenderer::class,
        'checkbox' => Renderers\ChoiceRenderer::class,
        'radiogroup' => Renderers\ChoiceRenderer::class,
        'dropdown' => Renderers\ChoiceRenderer::class,
        'tagbox' => Renderers\ChoiceRenderer::class,
        'ranking' => Renderers\ChoiceRenderer::class,
        'buttongroup' => Renderers\ChoiceRenderer::class,
        'rating' => Renderers\RatingRenderer::class,
        'boolean' => Renderers\BooleanRenderer::class,
        'matrix' => Renderers\MatrixRenderer::class,
        'matrixdropdown' => Renderers\MatrixRenderer::class,
        'matrixdynamic' => Renderers\MatrixRenderer::class,
        'file' => Renderers\FileRenderer::class,
        'signaturepad' => Renderers\SignatureRenderer::class,
        'image' => Renderers\ImageRenderer::class,
        'imagepicker' => Renderers\ImageRenderer::class,
        'html' => Renderers\HtmlRenderer::class,
        'expression' => Renderers\HtmlRenderer::class,
        'panel' => Renderers\PanelRenderer::class,
        'paneldynamic' => Renderers\PanelRenderer::class,
        'slider' => Renderers\SliderRenderer::class,
    ];

    public function __construct(
        protected array $surveyJson,
        protected array $responseData,
    ) {
        $this->locale = app()->getLocale();
        $this->storageDisk = config('survey-js.file_upload.disk') ?? config('filesystems.default');
    }

    public static function make(array $surveyJson, array $responseData): static
    {
        return new static($surveyJson, $responseData);
    }

    /**
     * Restructure flat SurveyJS response data into a page-ordered structure.
     */
    public static function structureData(array $surveyJson, array $responseData, ?string $locale = null): array
    {
        $instance = static::make($surveyJson, $responseData);

        if ($locale) {
            $instance->locale($locale);
        }

        return $instance->toExportData();
    }

    public static function registerRenderer(string $type, string $rendererClass): void
    {
        static::$customRenderers[$type] = $rendererClass;
    }

    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function theme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function showUnanswered(bool $show = true): static
    {
        $this->showUnanswered = $show;

        return $this;
    }

    public function showPageTitles(bool $show = true): static
    {
        $this->showPageTitles = $show;

        return $this;
    }

    public function showQuestionNumbers(bool $show = true): static
    {
        $this->showQuestionNumbers = $show;

        return $this;
    }

    public function showPageBreaks(bool $show = true): static
    {
        $this->showPageBreaks = $show;

        return $this;
    }

    public function showHeader(bool $show = true): static
    {
        $this->showHeader = $show;

        return $this;
    }

    public function showFooter(bool $show = true): static
    {
        $this->showFooter = $show;

        return $this;
    }

    public function headerView(string $view): static
    {
        $this->headerView = $view;

        return $this;
    }

    public function footerView(string $view): static
    {
        $this->footerView = $view;

        return $this;
    }

    public function unansweredText(string $text): static
    {
        $this->unansweredText = $text;

        return $this;
    }

    public function viewData(array $data): static
    {
        $this->viewData = $data;

        return $this;
    }

    public function disk(?string $disk): static
    {
        $this->storageDisk = $disk;

        return $this;
    }

    public function dateFormat(string $format): static
    {
        $this->dateFormat = $format;

        return $this;
    }

    public function datetimeFormat(string $format): static
    {
        $this->datetimeFormat = $format;

        return $this;
    }

    public function timeFormat(string $format): static
    {
        $this->timeFormat = $format;

        return $this;
    }

    public function toArray(): array
    {
        $questionNumber = 0;
        $renderedPages = [];

        foreach ($this->surveyJson['pages'] ?? [] as $page) {
            $questions = $this->resolveElements($page['elements'] ?? [], $questionNumber);

            $renderedPages[] = [
                'title' => $this->resolveLocaleString($page['title'] ?? null),
                'description' => $this->resolveLocaleString($page['description'] ?? null),
                'questions' => $questions,
                'rows' => $this->groupIntoRows($questions),
            ];
        }

        return [
            'title' => $this->resolveLocaleString($this->surveyJson['title'] ?? null),
            'description' => $this->resolveLocaleString($this->surveyJson['description'] ?? null),
            'pages' => $renderedPages,
        ];
    }

    public function render(): string
    {
        return $this->toView()->render();
    }

    public function toView(): View
    {
        return view('survey-js::rendering.survey', array_merge([
            'survey' => $this->toArray(),
            'css' => $this->getCss(),
            'theme' => $this->theme,
            'showUnanswered' => $this->showUnanswered,
            'showPageTitles' => $this->showPageTitles,
            'showQuestionNumbers' => $this->showQuestionNumbers,
            'showPageBreaks' => $this->showPageBreaks,
            'showHeader' => $this->showHeader,
            'showFooter' => $this->showFooter,
            'headerView' => $this->headerView,
            'footerView' => $this->footerView,
            'unansweredText' => $this->unansweredText,
        ], $this->viewData));
    }

    public function toResponse(): Response
    {
        return new Response($this->render(), 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * Export structured data mirroring the survey JSON definition.
     *
     * Pages and questions are in the exact order of the survey definition.
     * Panels and paneldynamic entries are nested.
     * Only data-relevant fields are included (no rendering artifacts).
     */
    public function toExportData(): array
    {
        $export = [
            'title' => $this->resolveLocaleString($this->surveyJson['title'] ?? null),
            'description' => $this->resolveLocaleString($this->surveyJson['description'] ?? null),
            'pages' => [],
        ];

        foreach ($this->surveyJson['pages'] ?? [] as $page) {
            $export['pages'][] = [
                'name' => $page['name'] ?? null,
                'title' => $this->resolveLocaleString($page['title'] ?? null),
                'description' => $this->resolveLocaleString($page['description'] ?? null),
                'questions' => $this->exportElements($page['elements'] ?? []),
            ];
        }

        return $export;
    }

    /**
     * Export structured data as a JSON string.
     */
    public function toExportJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toExportData(), $flags);
    }

    protected function exportElements(array $elements): array
    {
        $questions = [];

        foreach ($elements as $element) {
            $type = $element['type'] ?? 'text';
            $name = $element['name'] ?? null;
            $value = $name ? ($this->responseData[$name] ?? null) : null;

            // Skip decorative elements (html, image) — they have no response data
            if (in_array($type, ['html', 'image'])) {
                continue;
            }

            $entry = [
                'name' => $name,
                'type' => $type,
                'title' => $this->resolveLocaleString($element['title'] ?? $element['name'] ?? null),
            ];

            if ($type === 'panel') {
                $entry['questions'] = $this->exportElements($element['elements'] ?? []);
            } elseif ($type === 'paneldynamic') {
                $entry['entries'] = $this->exportPanelDynamic($element, $value);
            } else {
                $entry['value'] = $value;

                // Add formatted display value via renderer
                $rendererClass = static::$customRenderers[$type]
                    ?? static::$defaultRenderers[$type]
                    ?? Renderers\TextRenderer::class;

                $renderer = new $rendererClass($element, $value, $this->locale, $this->responseData, $this->storageDisk, $this->getRendererOptions());
                $displayValue = $renderer->getDisplayValue();

                if ($displayValue !== $value) {
                    $entry['displayValue'] = $displayValue;
                }
            }

            $questions[] = $entry;
        }

        return $questions;
    }

    protected function exportPanelDynamic(array $element, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $entries = [];

        foreach ($value as $index => $panelData) {
            $entry = [
                'index' => $index,
                'questions' => [],
            ];

            foreach ($element['templateElements'] ?? [] as $templateElement) {
                $templateName = $templateElement['name'] ?? null;
                $templateType = $templateElement['type'] ?? 'text';
                $templateValue = $templateName ? ($panelData[$templateName] ?? null) : null;

                if (in_array($templateType, ['html', 'image'])) {
                    continue;
                }

                $questionEntry = [
                    'name' => $templateName,
                    'type' => $templateType,
                    'title' => $this->resolveLocaleString($templateElement['title'] ?? $templateElement['name'] ?? null),
                    'value' => $templateValue,
                ];

                $entry['questions'][] = $questionEntry;
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    protected function resolveElements(array $elements, int &$questionNumber): array
    {
        $questions = [];

        foreach ($elements as $element) {
            $type = $element['type'] ?? 'text';
            $name = $element['name'] ?? null;
            $value = $name ? ($this->responseData[$name] ?? null) : null;

            $rendererClass = static::$customRenderers[$type]
                ?? static::$defaultRenderers[$type]
                ?? Renderers\TextRenderer::class;

            $renderer = new $rendererClass($element, $value, $this->locale, $this->responseData, $this->storageDisk, $this->getRendererOptions());

            $isDecorative = in_array($type, ['panel', 'paneldynamic', 'html', 'image']);

            if (! $isDecorative) {
                $questionNumber++;
            }

            $data = $renderer->toArray();
            $data['number'] = $isDecorative ? null : $questionNumber;
            $data['startWithNewLine'] = $element['startWithNewLine'] ?? true;

            if ($type === 'panel' && isset($element['elements'])) {
                $data['elements'] = $this->resolveElements($element['elements'], $questionNumber);
                $data['rows'] = $this->groupIntoRows($data['elements']);
            } elseif ($type === 'paneldynamic' && isset($element['templateElements'])) {
                $data['panels'] = $this->resolvePanelDynamic($element, $value);
            }

            $questions[] = $data;
        }

        return $questions;
    }

    protected function resolvePanelDynamic(array $element, mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $panels = [];

        foreach ($value as $index => $panelData) {
            $panelQuestions = [];

            foreach ($element['templateElements'] ?? [] as $templateElement) {
                $name = $templateElement['name'] ?? null;
                $elementValue = $name ? ($panelData[$name] ?? null) : null;
                $type = $templateElement['type'] ?? 'text';

                $rendererClass = static::$customRenderers[$type]
                    ?? static::$defaultRenderers[$type]
                    ?? Renderers\TextRenderer::class;

                $renderer = new $rendererClass($templateElement, $elementValue, $this->locale, $panelData, $this->storageDisk, $this->getRendererOptions());
                $data = $renderer->toArray();
                $data['number'] = null;
                $data['startWithNewLine'] = $templateElement['startWithNewLine'] ?? true;
                $panelQuestions[] = $data;
            }

            $panels[] = [
                'index' => $index,
                'title' => $this->resolveLocaleString($element['templateTitle'] ?? null),
                'questions' => $panelQuestions,
                'rows' => $this->groupIntoRows($panelQuestions),
            ];
        }

        return $panels;
    }

    protected function groupIntoRows(array $questions): array
    {
        $rows = [];
        $currentRow = [];

        foreach ($questions as $question) {
            if ($question['startWithNewLine'] ?? true) {
                if (! empty($currentRow)) {
                    $rows[] = $currentRow;
                }
                $currentRow = [$question];
            } else {
                $currentRow[] = $question;
            }
        }

        if (! empty($currentRow)) {
            $rows[] = $currentRow;
        }

        return $rows;
    }

    protected function getRendererOptions(): array
    {
        return [
            'dateFormat' => $this->dateFormat,
            'datetimeFormat' => $this->datetimeFormat,
            'timeFormat' => $this->timeFormat,
        ];
    }

    protected function getCss(): string
    {
        $path = __DIR__ . '/../../resources/css/rendering.css';

        return file_exists($path) ? file_get_contents($path) : '';
    }

    public function resolveLocaleString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value[$this->locale] ?? $value['default'] ?? reset($value) ?: null;
        }

        return (string) $value;
    }
}
