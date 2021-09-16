<?php

namespace IDCH\MailcoachPostalFeedback;

use IDCH\MailcoachPostalFeedback\PostalEvents\ClickEvent;
use IDCH\MailcoachPostalFeedback\PostalEvents\PostalEvent;
use IDCH\MailcoachPostalFeedback\PostalEvents\OpenEvent;
use IDCH\MailcoachPostalFeedback\PostalEvents\OtherEvent;
use IDCH\MailcoachPostalFeedback\PostalEvents\PermanentBounceEvent;

class PostalEventFactory
{
    protected static array $postalEvents = [
        ClickEvent::class,
        OpenEvent::class,
        PermanentBounceEvent::class,
    ];

    public static function createForPayload(array $payload): PostalEvent
    {
        $postalEvent = collect(static::$postalEvents)
            ->map(fn (string $postalEventClass) => new $postalEventClass($payload))
            ->first(fn (PostalEvent $postalEvent) => $postalEvent->canHandlePayload());

        return $postalEvent ?? new OtherEvent($payload);
    }
}
