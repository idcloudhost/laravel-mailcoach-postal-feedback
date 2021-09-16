<?php

namespace IDCH\MailcoachPostalFeedback\PostalEvents;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Spatie\Mailcoach\Domain\Shared\Models\Send;

abstract class PostalEvent
{
    protected array $payload;

    protected string $event;

    public function __construct(array $payload)
    {
        $this->payload = $payload;

        $this->event = Arr::get($payload, 'event');
    }

    abstract public function canHandlePayload(): bool;

    abstract public function handle(Send $send);

    public function getTimestamp(): ?DateTimeInterface
    {
        $timestamp = $this->payload['timestamp'];

        return $timestamp ? Carbon::createFromTimestamp($timestamp)->setTimezone(config('app.timezone')) : null;
    }
}
