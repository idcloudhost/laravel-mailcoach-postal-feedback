<?php

namespace IDCH\MailcoachPostalFeedback\PostalEvents;

use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

class PermanentBounceEvent extends PostalEvent
{
    public function canHandlePayload(): bool
    {
        return $this->event === 'MessageBounced';
    }

    public function handle(Send $send)
    {
        $data = $this->payload['payload'];

        if ($data['original_message']['to'] !== $send->subscriber->email) {
            return;
        }

        $send->registerBounce($this->getTimestamp());
    }
}
