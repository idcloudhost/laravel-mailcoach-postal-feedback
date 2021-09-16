<?php

namespace IDCH\MailcoachPostalFeedback\Tests;

use Illuminate\Support\Facades\Route;

class RouteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Route::postalFeedback('postal-feedback');

        config()->set('mailcoach.postal_feedback.signing_secret', 'secret');
    }

    /** @test */
    public function it_provides_a_route_macro_to_handle_webhooks()
    {
        $this->withoutExceptionHandling();

        $payload = $this->getStub('openWebhookContent');


        $this
            ->withHeaders([
                'x-postal-signature' => 'apiKeyAiC',
            ])
            ->post('postal-feedback?secret=secret', $payload)
            ->assertSuccessful();
    }

    /** @test */
    public function it_will_not_accept_calls_with_an_invalid_signature()
    {
        $payload = $this->getStub('openWebhookContent');

        $this
            ->post('postal-feedback?secret=incorrect_secret')
            ->assertStatus(500);
    }
}
