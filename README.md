# Filament SurveyJS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jibaymcs/survey-js.svg?style=flat-square)](https://packagist.org/packages/jibaymcs/survey-js)
[![Total Downloads](https://img.shields.io/packagist/dt/jibaymcs/survey-js.svg?style=flat-square)](https://packagist.org/packages/jibaymcs/survey-js)

A [SurveyJS](https://surveyjs.io) integration for [FilamentPHP v5](https://filamentphp.com). Render and build dynamic surveys directly in your Filament panels with full theme integration, auto-save, versioning, file uploads, and more.

**Three components included:**

- **`SurveyJSFormField`** — Render a survey in a Filament form (respondent view)
- **`SurveyJSCreatorField`** — Embed the SurveyJS Creator editor (builder view)
- **`SurveyRenderer`** — Generate standalone, print-friendly HTML from survey responses (no JS required)

---

## Requirements

- PHP 8.2+
- Laravel 11+
- Filament v5

## Installation

```bash
composer require jibaymcs/survey-js
```

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels, follow the instructions in the [Filament Docs](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme) first.

Add the plugin's views to your theme CSS file:

```css
@source '../../../../vendor/jibaymcs/survey-js/resources/**/*.blade.php';
```

Publish and run the migrations (required for versioning):

```bash
php artisan vendor:publish --tag="survey-js-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="survey-js-config"
```

Optionally, publish the views:

```bash
php artisan vendor:publish --tag="survey-js-views"
```

---

## Form Field — `SurveyJSFormField`

### Basic Usage

```php
use JibayMcs\SurveyJs\Forms\SurveyJSFormField;

SurveyJSFormField::make('survey_data')
    ->survey([
        'pages' => [
            [
                'name' => 'page1',
                'title' => 'About You',
                'elements' => [
                    ['type' => 'text', 'name' => 'name', 'title' => 'Your name'],
                    ['type' => 'rating', 'name' => 'satisfaction', 'title' => 'How satisfied are you?'],
                ],
            ],
        ],
    ])
```

The `survey()` method accepts an array, a JSON string, or a Closure:

```php
// From a JSON file
->survey(fn () => json_decode(file_get_contents(storage_path('surveys/my-survey.json')), true))

// From a JSON string
->survey('{"pages": [...]}')

// From an array
->survey($surveyArray)
```

> **Tip:** You can build your survey JSON for free using the [online Survey Creator](https://surveyjs.io/create-free-survey) — no license required. Export the JSON and pass it to `survey()`.

### Display Options

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->panelless()            // Remove panel borders (flat look)
    ->transparent()          // Transparent background
    ->readOnly()             // Read-only mode (also works with Filament's ->disabled())
    ->locale('fr')           // SurveyJS UI locale (defaults to APP_LOCALE)
    ->contained()            // Wrap in a Filament fieldset with dynamic title (default: true)
    ->contained(withTitle: false)  // Fieldset without title legend
    ->contained(false)       // No fieldset wrapper
```

### Custom Theme

Apply a custom SurveyJS theme JSON to override the default Filament-matching theme:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    // From an array
    ->theme($themeArray)
    // From a JSON string
    ->theme('{"cssVariables": {"--sjs-general-backcolor": "#ffffff"}, ...}')
    // From a Closure
    ->theme(fn () => json_decode(file_get_contents(storage_path('surveys/my-theme.json')), true))
```

> **Tip:** You can build custom themes using the [SurveyJS Theme Editor](https://surveyjs.io/form-library/documentation/manage-default-themes-and-styles) and export the JSON.

When a custom theme is set, the automatic Filament light/dark theme switching is bypassed — your theme is applied as-is.

### Navigation Buttons

Navigation uses Filament's `<x-filament::button>` with full color customization:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->showNavigationButtons()     // Show prev/next/complete buttons (default: true)
    ->showNavigationButtons(false, autoAdvance: true) // Hide buttons, auto-advance when page is complete
    ->showPrevButton(false)       // Hide the "Previous" button
    ->nativeNavigation()          // Use SurveyJS built-in navigation instead of Filament buttons
    ->pageNextText('Next step')   // Custom button text
    ->pagePrevText('Go back')
    ->completeText('Submit')
    ->prevButtonColor('gray')     // Filament color names
    ->nextButtonColor('primary')
    ->completeButtonColor('success')
```

### Progress Bar

```php
use JibayMcs\SurveyJs\Enums\ProgressBarLocation;

SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->showProgressBar()                              // Native SurveyJS dot progress bar
    ->showProgressBar(hasPercent: true)               // Percentage progress bar with animated fill
    ->showProgressBar(color: 'success')               // Custom color (Filament color name, hex, or Color::*)
    ->progressBarLocation(ProgressBarLocation::Top)   // Top, Bottom, or Both
```

### Validation & Errors

```php
use JibayMcs\SurveyJs\Enums\CheckErrorsMode;

SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->required()                                    // Filament validation: survey data must not be empty
    ->allFieldsRequired()                           // Make all SurveyJS questions required
    ->checkErrorsMode(CheckErrorsMode::OnNextPage)  // Validate on page change
    // Also: OnValueChanged, OnComplete
```

### Auto-Advance

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->autoAdvance()   // Automatically go to next page when all questions are answered
```

### Signature Pad

Customize the pen color for `signaturepad` question types:

```php
use Filament\Support\Colors\Color;

SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->signaturePenColor(Color::Blue)    // Filament Color constant
    ->signaturePenColor('primary')      // Named Filament color
    ->signaturePenColor('#1e40af')      // Hex value
```

### File Uploads

Enable server-side file storage for `file` and `signaturepad` question types:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->fileUpload(
        disk: 's3',                                    // Storage disk (default: filesystems.default)
        directory: 'survey-files',                     // Upload directory (default: 'survey-uploads')
        visibility: 'private',                         // 'public' or 'private' (default: 'private')
        maxSize: 5 * 1024 * 1024,                      // Max size in bytes (5MB)
        acceptedTypes: ['.pdf', '.docx', 'image/*'],   // Accepted MIME types
    )
```

Upload routes are protected by the `web` middleware and a configurable auth guard (see [Configuration](#configuration)).

### Completion Callbacks

```php
use Filament\Notifications\Notification;

SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->onComplete(function (array $data, $record, $component) {
        // Called when the survey is completed
        // $data = survey responses
        // $record = Eloquent model (if available)
    })
    ->completeNotification(
        Notification::make()
            ->title('Survey completed!')
            ->success()
    )
```

### Survey JSON Options

Inject any root-level [SurveyJS property](https://surveyjs.io/form-library/documentation/api-reference/survey-data-model) programmatically:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    // Single option
    ->option('logo', 'https://example.com/logo.png')
    ->option('logoHeight', '64')
    ->option('questionDescriptionLocation', 'underInput')
    ->option('showQuestionNumbers', 'off')
    // Batch options
    ->options([
        'logo' => 'https://example.com/logo.png',
        'logoHeight' => '64',
        'logoFit' => 'contain',
        'completedBeforeHtml' => '<h3>You already completed this survey.</h3>',
    ])
```

#### Completed HTML

A dedicated method for the `completedHtml` property with Blade view support:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    // From a Blade view
    ->completedHtml(view('surveys.completed', ['name' => $user->name]))
    // From a raw HTML string
    ->completedHtml('<h3>Thank you for your participation!</h3>')
    // From a Closure (has access to Filament's $record, $state, etc.)
    ->completedHtml(fn ($record) => view('surveys.completed', ['employee' => $record]))
```

### Auto-Save

Automatically save survey data to the database on every change:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->autoSave()              // Enable auto-save (writes to $record directly)
    ->autoSave(debounce: 1000) // Custom debounce in ms (default: 500)
```

> **Note:** Auto-save requires the form to have a bound `$record` (edit forms).

### Versioning

Track every survey response as a polymorphic version:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->versioning()                          // Save a version on completion
    ->versioning(onEveryChange: true)       // Save a version on every change
    ->versioning(onEveryChange: true, debounce: 2000) // With custom debounce
```

Add the trait to your Eloquent model:

```php
use JibayMcs\SurveyJs\Concerns\HasSurveyVersions;

class Employee extends Model
{
    use HasSurveyVersions;
}
```

Query versions:

```php
$employee->surveyVersions;                             // All versions
$employee->surveyVersionsFor('survey_data');           // Versions for a specific field
$employee->latestSurveyVersion('survey_data');         // Latest version
$employee->completedSurveyVersions('survey_data');     // Only completed versions
```

### Full Example

```php
use JibayMcs\SurveyJs\Forms\SurveyJSFormField;
use JibayMcs\SurveyJs\Enums\{CheckErrorsMode, ProgressBarLocation};
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;

SurveyJSFormField::make('survey_data')
    ->survey(fn () => json_decode(file_get_contents(storage_path('surveys/evaluation.json')), true))
    ->locale('fr')
    ->contained()
    ->panelless()
    ->theme(fn () => json_decode(file_get_contents(storage_path('surveys/custom-theme.json')), true))
    ->option('logo', 'https://example.com/logo.png')
    ->option('questionDescriptionLocation', 'underInput')
    ->completedHtml(view('surveys.completed'))
    ->showProgressBar(hasPercent: true, color: 'primary')
    ->progressBarLocation(ProgressBarLocation::Top)
    ->checkErrorsMode(CheckErrorsMode::OnNextPage)
    ->allFieldsRequired()
    ->signaturePenColor(Color::Blue)
    ->fileUpload(disk: 'local', directory: 'surveys/files', maxSize: 10 * 1024 * 1024)
    ->autoSave(debounce: 1000)
    ->versioning(onEveryChange: true)
    ->nextButtonColor('primary')
    ->completeButtonColor('success')
    ->completeText('Submit evaluation')
    ->onComplete(function (array $data, $record) {
        $record->update(['evaluation_completed_at' => now()]);
    })
    ->completeNotification(
        Notification::make()->title('Evaluation saved!')->success()
    )
    ->required()
```

---

## Creator Field — `SurveyJSCreatorField`

> [!IMPORTANT]
> The SurveyJS Creator is a **commercial product** that requires a [SurveyJS license](https://surveyjs.io/licensing). The Creator JS is **not bundled** with this plugin. You must install the Creator npm dependencies separately and build the assets yourself.

### Creator Installation

```bash
php artisan surveyjs:install-creator
```

This command installs the Creator npm dependencies and compiles the assets automatically.

### License Key

Add your SurveyJS license key to `.env`:

```env
SURVEYJS_LICENSE_KEY=your-license-key-here
```

The license key is required for the Creator and removes the SurveyJS watermark in production.

### Basic Usage

```php
use JibayMcs\SurveyJs\Forms\SurveyJSCreatorField;

SurveyJSCreatorField::make('survey_json')
```

The Creator stores the survey JSON schema in the field's state via `$entangle`.

### Tabs

Control which Creator tabs are visible:

```php
SurveyJSCreatorField::make('survey_json')
    ->showDesignerTab()         // Visual designer (default: true)
    ->showPreviewTab()          // Live preview (default: true)
    ->showJsonEditorTab()       // Raw JSON editor (default: true)
    ->showLogicTab()            // Conditional logic editor
    ->showTranslationTab()      // Multi-language translation editor
    ->showThemeTab()            // Theme customization
```

### Question Types

Restrict available question types using the `QuestionType` enum:

```php
use JibayMcs\SurveyJs\Enums\QuestionType;

SurveyJSCreatorField::make('survey_json')
    ->questionTypes([
        QuestionType::Text,
        QuestionType::Checkbox,
        QuestionType::Radiogroup,
        QuestionType::Dropdown,
        QuestionType::Rating,
        QuestionType::Boolean,
        QuestionType::File,
        QuestionType::SignaturePad,
    ])
```

Available types: `Text`, `Comment`, `MultipleText`, `Checkbox`, `Radiogroup`, `Dropdown`, `Tagbox`, `Ranking`, `ButtonGroup`, `Rating`, `Boolean`, `Slider`, `Matrix`, `MatrixDropdown`, `MatrixDynamic`, `File`, `SignaturePad`, `Image`, `ImagePicker`, `ImageMap`, `Html`, `Expression`, `Panel`, `PanelDynamic`, and more.

### Toolbox

```php
SurveyJSCreatorField::make('survey_json')
    ->toolboxLocation('right')             // 'left', 'right', or 'sidebar'
    ->toolboxCompact()                     // Compact mode (icons only)
    ->toolboxSearchEnabled()               // Enable search in toolbox
    ->toolboxShowCategoryTitles()          // Show category headers
```

### Page Edit Mode

```php
SurveyJSCreatorField::make('survey_json')
    ->pageEditMode('standard')   // 'standard', 'single', or 'bypage'
```

### Auto-Save & Callbacks

```php
SurveyJSCreatorField::make('survey_json')
    ->autoSave(delay: 2000)       // Auto-save with delay in ms
    ->onModified(function (array $json, $record, $component) {
        // Called on every modification
    })
    ->onSave(function (array $json, $record, $component) {
        // Called on explicit save (Ctrl+S)
    })
```

### Versioning

```php
SurveyJSCreatorField::make('survey_json')
    ->versioning()   // Save a version on every schema change
```

### Other Options

```php
SurveyJSCreatorField::make('survey_json')
    ->locale('fr')       // Creator UI locale
    ->readOnly()         // Disable editing
```

---

## Survey Renderer — `SurveyRenderer`

Generate standalone, print-friendly HTML from a SurveyJS JSON schema and response data. The output is a self-contained HTML document with inline CSS — no JavaScript required. Ideal for PDF generation, printing, archiving, or displaying read-only results.

### Basic Usage

```php
use JibayMcs\SurveyJs\Rendering\SurveyRenderer;

// Get the rendered HTML string
$html = SurveyRenderer::make($surveyJson, $responseData)->render();

// Return as an HTTP response
return SurveyRenderer::make($surveyJson, $responseData)->toResponse();

// Get a Blade View instance
$view = SurveyRenderer::make($surveyJson, $responseData)->toView();
```

### Display Options

```php
SurveyRenderer::make($surveyJson, $responseData)
    ->locale('fr')                    // Locale for titles, dates, etc. (default: app locale)
    ->theme('filament')               // 'filament', 'minimal', or 'print'
    ->showUnanswered(false)           // Hide unanswered questions (default: true)
    ->unansweredText('N/A')           // Text for unanswered questions (default: '—')
    ->showPageTitles(false)           // Hide page titles
    ->showQuestionNumbers(false)      // Hide question numbers
    ->showPageBreaks(false)           // Disable page breaks between pages
    ->render();
```

### Themes

Three built-in themes are available:

- **`filament`** — Matches Filament's design system (indigo primary, gray accents)
- **`minimal`** — Black & white with minimal styling
- **`print`** — Optimized for printing (smaller font, no backgrounds, high-contrast borders)

### Date & Time Formatting

Format date, datetime, and time values using [Carbon's `isoFormat()`](https://carbon.nesbot.com/docs/#api-localization) (CLDR patterns):

```php
SurveyRenderer::make($surveyJson, $responseData)
    ->locale('fr')
    ->dateFormat('L')           // Default: 'L' (e.g. 02/04/2026)
    ->datetimeFormat('L LT')    // Default: 'L LT' (e.g. 02/04/2026 14:30)
    ->timeFormat('LT')          // Default: 'LT' (e.g. 14:30)
    ->render();
```

### File & Signature Rendering

Files and signatures stored on a private disk are automatically resolved to inline base64 data URIs, making the HTML fully self-contained:

```php
SurveyRenderer::make($surveyJson, $responseData)
    ->disk('local')    // Storage disk for resolving file paths (default: config value)
    ->render();
```

### Header & Footer

```php
SurveyRenderer::make($surveyJson, $responseData)
    ->showHeader()                         // Show survey title header (default: true)
    ->showFooter()                         // Show footer (default: false)
    ->headerView('my-package::header')     // Custom Blade view for the header
    ->footerView('my-package::footer')     // Custom Blade view for the footer
    ->render();
```

### Custom Renderers

Register custom renderers for specific question types:

```php
use JibayMcs\SurveyJs\Rendering\SurveyRenderer;
use JibayMcs\SurveyJs\Rendering\QuestionRenderer;

class MyCustomRenderer extends QuestionRenderer
{
    public function getDisplayValue(): mixed
    {
        return $this->value;
    }

    protected function getViewType(): string
    {
        return 'my-custom-type'; // maps to survey-js::rendering.types.my-custom-type
    }
}

// In a service provider
SurveyRenderer::registerRenderer('mycustomtype', MyCustomRenderer::class);
```

### Supported Question Types

| Category | Types |
|---|---|
| **Text** | `text`, `comment`, `multipletext` |
| **Choice** | `checkbox`, `radiogroup`, `dropdown`, `tagbox`, `ranking`, `buttongroup` |
| **Rating** | `rating` |
| **Boolean** | `boolean` |
| **Matrix** | `matrix`, `matrixdropdown`, `matrixdynamic` |
| **File** | `file`, `signaturepad` |
| **Media** | `image`, `imagepicker` |
| **Layout** | `panel`, `paneldynamic`, `html`, `expression` |
| **Slider** | `slider` |

### Layout Support

The renderer respects SurveyJS layout properties:

- **`startWithNewLine: false`** — Questions are rendered side-by-side using flexbox
- **`titleLocation: "hidden"`** — Question title is hidden
- **`showNumber: false`** — Question number is hidden
- **`visible: false`** on matrix columns — Column is excluded from rendering

### Structured Data Export

SurveyJS stores response data as a flat key-value object (`{ "question1": "value", ... }`). The `toExportData()` method restructures it into a clean, page-ordered format that mirrors the survey JSON definition — ideal for APIs, archiving, or storing a readable version alongside the raw data.

```php
use JibayMcs\SurveyJs\Rendering\SurveyRenderer;

// Get structured array
$structured = SurveyRenderer::make($surveyJson, $responseData)
    ->locale('fr')
    ->toExportData();

// Get structured JSON string
$json = SurveyRenderer::make($surveyJson, $responseData)
    ->locale('fr')
    ->toExportJson();

// Quick static helper (no fluent config needed)
$structured = SurveyRenderer::structureData($surveyJson, $responseData, 'fr');
```

Output format:

```json
{
  "title": "Annual Review",
  "pages": [
    {
      "name": "page1",
      "title": "General Information",
      "questions": [
        { "name": "question1", "type": "text", "title": "Date", "value": "2026-03-04", "displayValue": "4 mars 2026" },
        {
          "name": "question2", "type": "panel", "title": "Employee",
          "questions": [
            { "name": "q3", "type": "text", "title": "Last name", "value": "Doe" }
          ]
        },
        {
          "name": "question4", "type": "matrixdynamic", "title": "Objectives",
          "entries": [
            {
              "index": 0,
              "questions": [
                { "name": "col1", "type": "comment", "title": "Objective", "value": "..." }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

Use it in an `onComplete` callback to save structured results:

```php
SurveyJSFormField::make('survey_data')
    ->survey($surveyJson)
    ->onComplete(function (array $data, $record) use ($surveyJson) {
        $record->update([
            'structured_results' => SurveyRenderer::structureData($surveyJson, $data, 'fr'),
        ]);
    })
```

### Full Example

```php
use JibayMcs\SurveyJs\Rendering\SurveyRenderer;

$html = SurveyRenderer::make($surveyJson, $responseData)
    ->locale('fr')
    ->theme('filament')
    ->disk('local')
    ->dateFormat('LL')
    ->datetimeFormat('LLL')
    ->showQuestionNumbers()
    ->showUnanswered()
    ->unansweredText('Non renseigne')
    ->showHeader()
    ->showFooter()
    ->render();

// Use in a controller
return response($html);

// Or generate a PDF with a library like Browsershot
Browsershot::html($html)->save('survey-result.pdf');
```

---

## Configuration

Published config file (`config/survey-js.php`):

```php
return [
    // SurveyJS license key (env: SURVEYJS_LICENSE_KEY)
    'license_key' => env('SURVEYJS_LICENSE_KEY'),

    // Default locale (null = app()->getLocale())
    'locale' => null,

    // Display defaults
    'panelless' => false,
    'transparent' => false,
    'contained' => true,
    'contained_with_title' => true,
    'read_only' => false,

    // Navigation & Progress
    'show_progress_bar' => false,
    'check_errors_mode' => null, // 'onNextPage', 'onValueChanged', 'onComplete'

    // Persistence
    'auto_save' => false,
    'auto_save_debounce' => 500,

    // File uploads
    'file_upload' => [
        'disk' => null,                  // null = filesystems.default
        'directory' => 'survey-uploads',
        'visibility' => 'private',
        'max_size' => null,              // Bytes, null = no limit
        'accepted_types' => null,        // ['.pdf', 'image/*']
        'auth_guard' => null,            // null = default guard
    ],
];
```

All config values serve as defaults. Explicit method calls on the field always take priority.

---

## Theming

The plugin automatically applies a Filament-matching theme to SurveyJS. Dark mode is fully supported and switches automatically with Filament's theme toggle.

The theme uses CSS variables from Filament's design system, so colors stay consistent with your panel's custom theme.

To use a fully custom SurveyJS theme instead, use the [`theme()`](#custom-theme) method — this bypasses the automatic Filament theme entirely.

---

## Translations

Built-in translations are provided for upload error messages in English, French, and Spanish. You can publish and customize them:

```bash
php artisan vendor:publish --tag="survey-js-translations"
```

SurveyJS's own UI strings (validation messages, button labels, etc.) are handled by SurveyJS's built-in i18n system via the `locale()` method. SurveyJS supports [50+ languages](https://surveyjs.io/form-library/documentation/survey-localization) out of the box.

---

## Testing

```bash
composer test
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [JibayMcs](https://github.com/JibayMcs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
