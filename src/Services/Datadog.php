<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use DataDog\DogStatsd;

/**
 * Class Datadog
 *
 * @package AirSlate\Datadog\Services
 */
class Datadog extends DogStatsd
{
    /**
     * @var array
     */
    private $tags = [];

    /**
     * {@inheritdoc}
     */
    public function send($data, $sampleRate = 1.0, $tags = null): void
    {
        $tags = $this->prepareTags(is_array($tags) ? $tags : []);
        parent::send($data, $sampleRate, $tags);

        $this->sendToStatsd($data, $sampleRate, $tags);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public function addTag(string $key, $value): void
    {
        if (!empty($key) && !empty($value)) {
            $this->tags[$key] = $value;
        }
    }

    /**
     * @param $data
     * @param float $sampleRate
     * @param array|null $tags
     *
     * @return void
     */
    protected function sendToStatsd($data, $sampleRate = 1.0, array $tags = null): void
    {
        $statsd = new Statsd();

        $statsd->addTag('env', env('APP_ENV'));

        $statsd->send($data, $sampleRate, $tags);
    }

    /**
     * @param array $tags
     *
     * @return array
     */
    private function prepareTags(array $tags): array
    {
        return array_merge($this->tags, $tags);
    }
}
