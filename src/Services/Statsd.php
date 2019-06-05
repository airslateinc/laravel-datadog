<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use DataDog\DogStatsd;

/**
 * Class Statsd
 *
 * @package AirSlate\Datadog\Services
 */
class Statsd extends DogStatsd
{
    /**
     * @var array
     */
    private $tags = [];

    /**
     * Statsd constructor.
     */
    public function __construct()
    {
        parent::__construct($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function send($data, $sampleRate = 1.0, $tags = null): void
    {
        $tags = $this->prepareTags(is_array($tags) ? $tags : []);
        parent::send($data, $sampleRate, $tags);
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
     * @return array
     */
    protected function getConfig(): array
    {
        $config = [];

        $statsdUrl = env('STATSD_URL', null);

        if ($statsdUrl) {
            $statsdUrl = parse_url($statsdUrl);

            if (isset($statsdUrl['host'])) {
                $config['host'] = $statsdUrl['host'];
            }

            if (isset($statsdUrl['port'])) {
                $config['port'] = $statsdUrl['port'];
            }
        }

        return $config;
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
