<?php

namespace IDCH\MailcoachPostalFeedback;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class PostalSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (empty($config->signingSecret) || !$request->header('x-postal-signature')) {
            return false;
        }

        return $request->get('secret') === $config->signingSecret;
    }
}
