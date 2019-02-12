<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Listeners;

use AirSlate\Datadog\Services\Datadog;

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
    private $datadog;

    /**
     * EventBusListener constructor.
     *
     * @param Datadog $datadog
     */
    public function __construct(Datadog $datadog)
    {
        $this->datadog = $datadog;
    }

    /**
     * @param mixed $event
     * @throws \Exception
     */
    public function handle($event) : void
    {
        if ($event instanceof \AirSlate\Event\Events\ProcessedEvent) {
            $this->datadog->timing('airslate.eventbus.receive', $this->getConsumerDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'processed',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\RejectedEvent) {
            $this->datadog->timing('airslate.eventbus.receive', $this->getConsumerDuration($event), 1, [
                'key' => $event->getRoutingKey(),
                'queue' => $event->getQueueName(),
                'status' => 'rejected',
            ]);
        } elseif ($event instanceof \AirSlate\Event\Events\RetryEvent) {
            $duration = 0.0; // measure only count of retries
            $this->datadog->timing('airslate.eventbus.receive', $duration, 1, [
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
            $this->datadog->increment('airslate.queue.job', 1, [
                'connection' => $event->job->getConnectionName(),
                'status' => 'processing',
            ]);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobProcessed) {
            $this->datadog->increment('airslate.queue.job', 1, [
                'connection' => $event->job->getConnectionName(),
                'status' => 'processed',
            ]);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobExceptionOccurred) {
            $this->datadog->increment('airslate.queue.job', 1, [
                'connection' => $event->job->getConnectionName(),
                'status' => 'exceptionOccurred',
            ]);
        } elseif ($event instanceof \Illuminate\Queue\Events\JobFailed) {
            $this->datadog->increment('airslate.queue.job', 1, [
                'connection' => $event->job->getConnectionName(),
                'status' => 'failed',
            ]);
        }
    }

    /**
     * @param mixed $event
     * @return float
     */
    private function getConsumerDuration($event): float
    {
        return method_exists($event, $event->getConsumerProcessDuration())
            ? (float) $event->getConsumerProcessDuration()
            : 0.0;
    }
}
