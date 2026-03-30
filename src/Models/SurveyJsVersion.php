<?php

namespace JibayMcs\SurveyJs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SurveyJsVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'versionable_type',
        'versionable_id',
        'field_name',
        'data',
        'completed',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'completed' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function versionable(): MorphTo
    {
        return $this->morphTo();
    }
}
