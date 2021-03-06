<?php

namespace IDCH\MailcoachPostalFeedback\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Spatie\Mailcoach\Database\Factories\SendFactory;
use Spatie\Mailcoach\Domain\Campaign\Enums\SendFeedbackType;
use Spatie\Mailcoach\Domain\Campaign\Events\WebhookCallProcessedEvent;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignClick;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignLink;
use Spatie\Mailcoach\Domain\Campaign\Models\CampaignOpen;
use Spatie\Mailcoach\Domain\Shared\Models\Send;
use Spatie\Mailcoach\Domain\Shared\Models\SendFeedbackItem;
use Spatie\WebhookClient\Models\WebhookCall;
use IDCH\MailcoachPostalFeedback\ProcessPostalWebhookJob;

class ProcessPostalWebhookJobTest extends TestCase
{
    private WebhookCall $webhookCall;

    private Send $send;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookCall = WebhookCall::create([
            'name' => 'postal',
            'payload' => $this->getStub('bounceWebhookContent'),
        ]);

        $this->send = SendFactory::new()->create([
            'transport_message_id' => '5817a64332f44_4ec93ff59e79d154565eb@app34.mail',
        ]);

        $this->send->campaign->update([
            'track_opens' => true,
            'track_clicks' => true,
        ]);
    }

    /** @test */
    public function it_processes_a_postal_bounce_webhook_call()
    {
        (new ProcessPostalWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, SendFeedbackItem::count());

        tap(SendFeedbackItem::first(), function (SendFeedbackItem $sendFeedbackItem) {
            $this->assertEquals(SendFeedbackType::BOUNCE, $sendFeedbackItem->type);
            $this->assertEquals(Carbon::createFromTimestamp(1521233195), $sendFeedbackItem->created_at);
            $this->assertTrue($this->send->is($sendFeedbackItem->send));
        });
    }

    /** @test */
    public function it_processes_a_postal_click_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('clickWebhookContent')]);
        (new ProcessPostalWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(1, CampaignLink::count());
        $this->assertEquals('http://aic.my.id', CampaignLink::first()->url);
        $this->assertCount(1, CampaignLink::first()->clicks);
        tap(CampaignLink::first()->clicks->first(), function (CampaignClick $campaignClick) {
            $this->assertEquals(Carbon::createFromTimestamp(1377075564), $campaignClick->created_at);
        });
    }

    /** @test */
    public function it_can_process_a_postal_open_webhook_call()
    {
        $this->webhookCall->update(['payload' => $this->getStub('openWebhookContent')]);
        (new ProcessPostalWebhookJob($this->webhookCall))->handle();

        $this->assertCount(1, $this->send->campaign->opens);
        tap($this->send->campaign->opens->first(), function (CampaignOpen $campaignOpen) {
            $this->assertEquals(Carbon::createFromTimestamp(1377047343), $campaignOpen->created_at);
        });
    }

    /** @test */
    public function it_fires_an_event_after_processing_the_webhook_call()
    {
        Event::fake();

        $this->webhookCall->update(['payload' => $this->getStub('openWebhookContent')]);
        (new ProcessPostalWebhookJob($this->webhookCall))->handle();

        Event::assertDispatched(WebhookCallProcessedEvent::class);
    }

    /** @test */
    public function it_will_not_handle_unrelated_events()
    {
        $this->webhookCall->update(['payload' => $this->getStub('otherWebhookContent')]);
        (new ProcessPostalWebhookJob($this->webhookCall))->handle();

        $this->assertEquals(0, CampaignLink::count());
        $this->assertEquals(0, CampaignOpen::count());
        $this->assertEquals(0, SendFeedbackItem::count());
    }

    /** @test */
    public function it_does_nothing_when_it_cannot_find_the_transport_message_id()
    {
        $data = $this->webhookCall->payload;
        $data['payload']['message']['message-id'] = 'some-other-id';

        $this->webhookCall->update([
            'payload' => $data,
        ]);

        $job = new ProcessPostalWebhookJob($this->webhookCall);

        $job->handle();

        $this->assertEquals(0, SendFeedbackItem::count());
    }
}
