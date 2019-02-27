<?php
declare(strict_types=1);

namespace AirSlate\Datadog\ServiceProviders;

use AirSlate\Datadog\Services\Datadog;
use AirSlate\Datadog\Services\QueueJobMeter;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class DatadogProvider
 *
 * @package AirSlate\Datadog\ServiceProviders
 */
class DatadogProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                \dirname($this->configPath()) => $this->app->make('path.config')], 'datadog');
        }
        $this->mergeConfigFrom($this->configPath(), 'datadog');
    }

    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $config = $this->app->get('config')->get('datadog');

        $datadog = new Datadog([
            'host' => $config['statsd_server'] ?? '172.17.0.1',
            'port' => $config['statsd_port'] ?? 8125,
        ]);

        $this->app->singleton(Datadog::class, function () use ($datadog): Datadog {
            return $datadog;
        });

        $this->app->singleton(QueueJobMeter::class, function (): QueueJobMeter {
            return new QueueJobMeter();
        });

        $datadog->addTag('app', $config['application_name'] ?? 'unknown');

        $this->registerRouteMatchedListener($datadog);
    }

    /**
     * Return config path.
     *
     * @return string
     */
    private function configPath(): string
    {
        return \dirname(__DIR__) . '/config/datadog.php';
    }

    /**
     * @param Datadog $datadog
     *
     * @throws BindingResolutionException
     */
    private function registerRouteMatchedListener(Datadog $datadog): void
    {
        $this->app->make('router')->matched(function (RouteMatched $matched) use ($datadog) {
            $datadog->addTag('url', $matched->route->uri);
        });
    }
}
