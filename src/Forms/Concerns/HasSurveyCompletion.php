<?php

namespace JibayMcs\SurveyJs\Forms\Concerns;

use Closure;
use Filament\Notifications\Notification;

trait HasSurveyCompletion
{
    protected ?Closure $onCompleteCallback = null;

    protected ?Notification $completeNotification = null;

    public function onComplete(Closure $callback): static
    {
        $this->onCompleteCallback = $callback;

        return $this;
    }

    public function completeNotification(Notification $notification): static
    {
        $this->completeNotification = $notification;

        return $this;
    }
}
