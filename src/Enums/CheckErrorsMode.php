<?php

namespace JibayMcs\SurveyJs\Enums;

enum CheckErrorsMode: string
{
    case OnNextPage = 'onNextPage';
    case OnValueChanged = 'onValueChanged';
    case OnComplete = 'onComplete';
}
