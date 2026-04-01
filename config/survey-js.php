<?php

// config for JibayMcs/SurveyJs
return [

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Locale par defaut pour les textes d'interface SurveyJS (boutons, labels,
    | messages de validation, etc.). Si null, utilise app()->getLocale().
    | Exemples : 'fr', 'en', 'es', 'de'
    |
    */
    'locale' => null,

    /*
    |--------------------------------------------------------------------------
    | Affichage
    |--------------------------------------------------------------------------
    */
    'panelless' => false,
    'transparent' => false,
    'contained' => true,
    'contained_with_title' => true,
    'read_only' => false,

    /*
    |--------------------------------------------------------------------------
    | Navigation & Progression
    |--------------------------------------------------------------------------
    */
    'show_progress_bar' => false,
    'check_errors_mode' => null, // 'onNextPage', 'onValueChanged', 'onComplete'

    /*
    |--------------------------------------------------------------------------
    | Persistance
    |--------------------------------------------------------------------------
    */
    'auto_save' => false,
    'auto_save_debounce' => 500,

    /*
    |--------------------------------------------------------------------------
    | Upload de fichiers
    |--------------------------------------------------------------------------
    |
    | Configuration pour les questions SurveyJS de type 'file' et 'signaturepad'.
    | Activez fileUpload() sur le champ pour utiliser ces parametres.
    |
    */
    'file_upload' => [
        'disk' => null, // null = filesystems.default
        'directory' => 'survey-uploads',
        'visibility' => 'private', // 'public' ou 'private'
        'max_size' => null, // En octets. null = pas de limite
        'accepted_types' => null, // Ex: ['.pdf', '.docx', 'image/*']
        'auth_guard' => null, // null = guard par defaut. Ex: 'web', 'filament'
    ],

];
