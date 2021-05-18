<?php

declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use DataDog\DogStatsd;
use Illuminate\Support\Facades\Config;
use Psr\Log\LoggerInterface;
use Throwable;

class Datadog extends DogStatsd
{
    /** @var array<string, string> */
    private $tags = [];
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Datadog constructor.
     * @param mixed[] $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->logger = app('log');
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     **/
    public function send($data, $sampleRate = 1.0, $tags = null): void
    {
        $tags = $this->prepareTags(is_array($tags) ? $tags : null);

        try {
            parent::send($data, $sampleRate, $tags);
        } catch (Throwable $exception) {
            $this->logger->error(
                'Could not send data to Datadog Statsd',
                ['exception' => $exception]
            );
        }
    }

    /**
     * {@inheritdoc}
     * @phpstan-ignore-next-line
     */
    public function timing($stat, $time, $sampleRate = 1.0, $tags = null): void
    {
        try {
            parent::timing($stat, $time, $sampleRate, $tags);

            if (Config::get('datadog.is_send_increment_metric_with_timing_metric') !== false) {
                $this->increment($stat, $sampleRate, $tags);
            }
        } catch (Throwable $exception) {
            $this->logger->error(
                'Could not send timing data to Datadog Statsd',
                ['exception' => $exception]
            );
        }
    }

    public function addTag(string $key, string $value): void
    {
        if ($key !== '' && $value !== '') {
            $this->tags[$key] = $value;
        }
    }

    /**
     * @param array<string, string>|null $tags
     * @return array<string, string>
     */
    private function prepareTags(?array $tags): array
    {
        $tags = is_array($tags) ? $tags : [];

        return array_merge($this->tags, $tags);
    }
}
