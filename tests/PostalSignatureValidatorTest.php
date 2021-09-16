<?php

namespace IDCH\MailcoachPostalFeedback\Tests;

use Illuminate\Http\Request;
use IDCH\MailcoachPostalFeedback\PostalSignatureValidator;
use Spatie\WebhookClient\WebhookConfig;
use IDCH\MailcoachPostalFeedback\PostalWebhookConfig;

class PostalSignatureValidatorTest extends TestCase
{
    private WebhookConfig $config;

    private PostalSignatureValidator $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = PostalWebhookConfig::get();

        $this->validator = new PostalSignatureValidator();
    }

    private function validParams(array $overrides = []): array
    {
        return array_merge($this->addValidSignature([]), $overrides);
    }

    /** @test */
    public function it_requires_signature_data()
    {
        $request = new Request($this->validParams());

        $this->assertTrue($this->validator->isValid($request, $this->config));
    }

    /** @test * */
    public function it_fails_if_payload_is_missing()
    {
        $request = new Request($this->validParams([
            'payload' => [],
        ]));

        $this->assertFalse($this->validator->isValid($request, $this->config));
    }
}
