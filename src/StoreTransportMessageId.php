<?php

namespace IDCH\MailcoachPostalFeedback;

use Illuminate\Mail\Events\MessageSent;

use Illuminate\Support\Facades\Log;

class StoreTransportMessageId
{
    public function handle(MessageSent $event)
    {
        // Log::info('StoreTransportMessageId', ['data' => $event]);
    }
}
