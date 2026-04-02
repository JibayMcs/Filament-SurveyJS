# Filament SurveyJS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jibaymcs/survey-js.svg?style=flat-square)](https://packagist.org/packages/jibaymcs/survey-js)
[![Total Downloads](https://img.shields.io/packagist/dt/jibaymcs/survey-js.svg?style=flat-square)](https://packagist.org/packages/jibaymcs/survey-js)

A [SurveyJS](https://surveyjs.io) integration for [FilamentPHP v5](https://filamentphp.com). Render and build dynamic surveys directly in your Filament panels with full theme integration, auto-save, versioning, file uploads, and more.

**Two field components included:**

- **`SurveyJSFormField`** — Render a survey in a Filament form (respondent view)
- **`SurveyJSCreatorField`** — Embed the SurveyJS Creator editor (builder view)

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

### Navigation Buttons

Navigation uses Filament's `<x-filament::button>` with full color customization:

```php
SurveyJSFormField::make('survey_data')
    ->survey($json)
    ->showNavigationButtons()     // Show prev/next/complete buttons (default: true)
    ->showNavigationButtons(false, autoAdvance: true) // Hide buttons, auto-advance when page is complete
    ->showPrevButton(false)       // Hide the "Previous" button
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
    ->fileUpload()                           // Enable file uploads
    ->fileUploadDisk('s3')                   // Storage disk (default: filesystems.default)
    ->fileUploadDirectory('survey-files')    // Upload directory (default: 'survey-uploads')
    ->fileUploadVisibility('private')        // 'public' or 'private' (default: 'private')
    ->fileUploadMaxSize(5 * 1024 * 1024)    // Max size in bytes (5MB)
    ->fileUploadAcceptedTypes(['.pdf', '.docx', 'image/*'])  // Accepted MIME types
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
    ->showProgressBar(hasPercent: true, color: 'primary')
    ->progressBarLocation(ProgressBarLocation::Top)
    ->checkErrorsMode(CheckErrorsMode::OnNextPage)
    ->allFieldsRequired()
    ->signaturePenColor(Color::Blue)
    ->fileUpload()
    ->fileUploadDisk('local')
    ->fileUploadDirectory('surveys/files')
    ->fileUploadMaxSize(10 * 1024 * 1024)
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
