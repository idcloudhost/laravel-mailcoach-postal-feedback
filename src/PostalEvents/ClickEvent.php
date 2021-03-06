<?php

namespace IDCH\MailcoachPostalFeedback\PostalEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class ClickEvent extends PostalEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'MessageLinkClicked';
    }

    public function handle(Send $send)
    {
        $url = Arr::get($this->payload, 'payload.url');

        $send->registerClick($url, $this->getTimestamp());
    }
}
