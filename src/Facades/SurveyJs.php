<?php

namespace JibayMcs\SurveyJs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JibayMcs\SurveyJs\SurveyJs
 */
class SurveyJs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JibayMcs\SurveyJs\SurveyJs::class;
    }
}
