<?php

namespace JibayMcs\SurveyJs\Enums;

enum QuestionType: string
{
    // Saisie de texte
    case Text = 'text';
    case Comment = 'comment';
    case MultipleText = 'multipletext';

    // Choix
    case Checkbox = 'checkbox';
    case Radiogroup = 'radiogroup';
    case Dropdown = 'dropdown';
    case Tagbox = 'tagbox';
    case Ranking = 'ranking';
    case ButtonGroup = 'buttongroup';

    // Evaluation
    case Rating = 'rating';
    case Boolean = 'boolean';
    case Slider = 'slider';

    // Matrices
    case Matrix = 'matrix';
    case MatrixDropdown = 'matrixdropdown';
    case MatrixDynamic = 'matrixdynamic';

    // Media & fichiers
    case File = 'file';
    case SignaturePad = 'signaturepad';
    case Image = 'image';
    case ImagePicker = 'imagepicker';
    case ImageMap = 'imagemap';

    // Affichage
    case Html = 'html';
    case Expression = 'expression';

    // Conteneurs
    case Panel = 'panel';
    case PanelDynamic = 'paneldynamic';
}
