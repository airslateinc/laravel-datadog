<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Listeners;

use AirSlate\Datadog\Services\Datadog;
use AirSlate\Datadog\Services\QueueJobMeter;

/**
 * Class EventBusListener
 *
 * @package AirSlate\Datadog\Listenres
 */
class EventBusListener
{
    /**
     * @var Datadog
     */
    protected $datadog;

    /**
     * @var QueueJobMeter
     */
    protected $meter;

    /**
     * EventBusListener constructor.
     *
     * @param Datadog $datadog
     * @param QueueJobMeter $meter
     */
    public function __construct(Datadog $datadog, QueueJobMeter $meter)
    {
        $this->datadog = $datadog;
        $this->meter = $meter;
    }

    /**
     * @param mixed $event
     * @throws \Exception
     */
    public function handle($event): void
    {
        if ($event instanceof \AirSlate\Event\Events\ProcessedEvent) {
            $this->datadog->timing('airslate.eventbus.receive', $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'processed',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\RejectedEvent) {
            $this->datadog->timing('airslate.eventbus.receive', $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'rejected',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\RetryEvent) {
            $this->datadog->timing('airslate.eventbus.receive', $this->getDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'retried',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\SendEvent) {
            $this->datadog->increment('airslate.eventbus.send', 1, [
                'key' => $event->getRoutingKey(),
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\SendToQueueEvent) {
            $this->datadog->increment('airslate.eventbus.sendtoqueue', 1, [
                'queue' => $event->getQueueName(),
            ]);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobProcessing) {
            $this->meter->start($event->job);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobProcessed) {
            $this->datadog->timing('airslate.queue.job', $this->meter->stop($event->job), 1, [
                'status' => 'processed',
            ]);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobExceptionOccurred) {
            $this->datadog->timing('airslate.queue.job', $this->meter->stop($event->job), 1, [
                'status' => 'exceptionOccurred',
            ]);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobFailed) {
            $this->datadog->timing('airslate.queue.job', $this->meter->stop($event->job), 1, [
                'status' => 'failed',
            ]);
        }
    }

    /**
     * @param mixed $event
     * @return float
     */
    private function getDuration($event): float
    {
        return method_exists($event, 'getDuration') ? (float) $event->getDuration() : 0.0;
    }
}
