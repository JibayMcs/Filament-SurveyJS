# Changelog

All notable changes to `filament-surveyjs` will be documented in this file.

## 1.0.0 - 2026-04-02

### Form Field (`SurveyJSFormField`)

- **Survey rendering** — Render SurveyJS surveys in Filament forms with full `$entangle` state sync
- **Filament theme** — Automatic light/dark theme matching with Filament's design system
- **Display options** — `panelless()`, `transparent()`, `readOnly()`, `contained(withTitle:)`
- **Navigation** — Custom Filament buttons (prev/next/complete) with color customization
- **Progress bar** — Native dot progress bar + custom percentage progress bar with configurable color
- **Localization** — `locale()` method with fallback to `APP_LOCALE`, SurveyJS i18n (50+ languages)
- **Validation** — `required()`, `allFieldsRequired()`, `checkErrorsMode()`, auto-advance
- **File uploads** — Server-side upload/download/delete via Laravel Storage with signed URLs
- **Signature pad** — Custom `penColor` for signaturepad questions using Filament Color system
- **Auto-save** — Live persistence to database with configurable debounce
- **Versioning** — Polymorphic version tracking (on completion or on every change)
- **Completion** — `onComplete()` callback + `completeNotification()` with Filament Notification
- **State management** — `afterStateHydrated` normalization, `dehydrateState` cleanup of internal markers
- **Configuration** — Global defaults via `config/survey-js.php`, per-field overrides

### Creator Field (`SurveyJSCreatorField`)

- **Survey editor** — Embed the SurveyJS Creator with Filament theme integration
- **Tab control** — Show/hide Designer, Preview, JSON Editor, Logic, Translation, Theme tabs
- **Question types** — `questionTypes()` with `QuestionType` enum for IDE autocompletion (28 types)
- **Toolbox** — Location, compact mode, search, category titles
- **Auto-save** — Automatic JSON persistence with configurable delay
- **Callbacks** — `onModified()` and `onSave()` PHP callbacks
- **Versioning** — Reuses the polymorphic versioning system for schema versions
- **License key** — `SURVEYJS_LICENSE_KEY` via `.env`

### Infrastructure

- **Routes** — Protected upload/download/delete routes with `web` + `auth` middleware
- **Model** — `SurveyJsVersion` with polymorphic relations, `HasSurveyVersions` trait
- **Enums** — `CheckErrorsMode`, `ProgressBarLocation`, `QuestionType`
- **Translations** — Upload error messages in English, French, and Spanish
- **Config** — Comprehensive `config/survey-js.php` with all defaults
