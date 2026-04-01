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

];
