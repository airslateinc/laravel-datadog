# Datadog helper library for laravel

This library makes it simple to integrate Datadog into your laravel project

## Requirements

- PHP >= 7.1
- Laravel Framework ^5.6, ^6.0, ^7.0, ^8.0

## Installation

The library can be installed using Composer.

Add vcs repository url to the `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:airslateinc/laravel-datadog.git"
    }
]
```

Install

```bash
composer require airslate/laravel-datadog
```

### Setting service provider
**This package provide auto discovery for service provider** 

If Laravel package auto-discovery is disabled, add service providers manually.
There are two service providers you must add:
```
\AirSlate\Datadog\ServiceProviders\DatadogProvider::class
\AirSlate\Datadog\ServiceProviders\ComponentsProvider::class
```

### Publish client configuration:

```bash
php artisan vendor:publish --tag=datadog
```
###### Important for v3:
If you were using v2 version of this library you must run next code to recreate config file

```bash
php artisan vendor:publish --tag=datadog --force
```
or add next code to existing config 
```
'components' => [
    'http' => [
        \AirSlate\Datadog\Components\ResponseTimeComponent::class,
        \AirSlate\Datadog\Components\HttpQueryCounterComponent::class,
    ],
    'console' => [
        \AirSlate\Datadog\Components\JobTimingComponent::class,
        \AirSlate\Datadog\Components\JobQueryCounterComponent::class,
    ],
    'all' => [
        \AirSlate\Datadog\Components\CacheHitsComponent::class,
        \AirSlate\Datadog\Components\DbTransactionsComponent::class,
        \AirSlate\Datadog\Components\DbQueryExecutedComponent::class,
        \AirSlate\Datadog\Components\CustomEventsComponent::class,
        \AirSlate\Datadog\Components\MemoryPeakUsageComponent::class,
    ]
],
```

## For local, stand-alone service development
Add Datadog keys to docker/config/env.ctmpl
```
STATSD_HOST=172.17.0.1
STATSD_PORT=8125
```

Add datadog agent for your docker-compose.yml file
```yaml
datadog:
    container_name: as-infra-datadog
    image: datadog/docker-dd-agent
    ports:
      - 8125:8125/udp
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - /proc/:/host/proc/:ro
      - /sys/fs/cgroup/:/host/sys/fs/cgroup:ro
    environment:
      API_KEY: __enter__your__key__there
      SD_BACKEND: docker
      NON_LOCAL_TRAFFIC: "true"
```

## Add Default tags

```php
    $this->app->bind('datadog.company.tag', function() {
        return new Tag('company', 'airslate');
    });
    $this->app->tag('datadog.company.tag', DatadogProvider::DATADOG_TAG);
```



## Add custom events

You can add your own events with name and custom tags. You need: 
1. Add `AirSlate\Datadog\Events\DatadogEventInterface` or `AirSlate\Datadog\Events\DatadogEventExtendedInterface` interface to you event.
2. dispatch event by 
```php
 event(new YourEvent());
```

## Components

This is part of functionality that responsible for processing of the metric.
You can remove component by removing it from datadog.php config. Below you will find 
short description of each component.
   
Note:
_{application_namespace} - configurable through config file_

#### Component CacheHitsComponent
```
AirSlate\Datadog\Components\CacheHitsComponent
```
##### Metrics added by component: 
- {application_namespace}.cache.item
    ###### tags: 
    - status - hit|miss|del|put
    ###### type
    - increment


#### Component HttpQueryCounterComponent
```
AirSlate\Datadog\Components\HttpQueryCounterComponent
```
##### description
- sends gauge metric with amount of queries executed during http call
##### Metrics added by component:
- {application_namespace}.db.queries
    ###### tags: 
    - code - (http status code)
    - method - (http method)
    ###### type:
    - gauge

#### Component DbTransactionsComponent
```
AirSlate\Datadog\Components\DbTransactionsComponent
```
##### Metrics added by component:
- {application_namespace}.db.transaction
    ###### tags: 
    - status - begin|commit|rollback
    ###### type:
    - increment
    
#### Component DbQueryExecutedComponent
```
AirSlate\Datadog\Components\DbQueryExecutedComponent
```
##### Metrics added by component:
- {application_namespace}.db.query
    ###### tags: 
    - status - executed
    ###### type:
    - increment

#### Component JobQueryCounterComponent
```
AirSlate\Datadog\Components\JobQueryCounterComponent
```
##### Metrics added by component:
- {application_namespace}.queue.db.queries
    ###### tags: 
    - status - processed|exceptionOccurred|failed
    - queue - (queue name)
    - task - (job name)
    - exception - (exception class name)
    ###### type:
    - gauge

#### Component JobTimingComponent
```
AirSlate\Datadog\Components\JobTimingComponent
```
##### Metrics added by component:
- {application_namespace}.queue.job
    ###### tags: 
    - status - processed|exceptionOccurred|failed
    - queue - (queue name)
    - task - (job name)
    - exception - (exception class name)
    ###### type:
    - timing

#### Component JobTimingComponent
```
AirSlate\Datadog\Components\JobTimingComponent
```
##### Metrics added by component:
- {application_namespace}.memory_peak_usage
    ###### tags: 
    - code - (http response code)
    - method - (http request method)
    ###### type:
    - gauge
- {application_namespace}.memory_peak_usage_real
    ###### tags: 
    - code - (http response code)
    - method - (http request method)
    ###### type:
    - gauge

#### Component MemoryPeakUsageComponent
```
AirSlate\Datadog\Components\MemoryPeakUsageComponent
```
##### Metrics added by component:
- {application_namespace}.queue.job
    ###### tags: 
    - status - processed|exceptionOccurred|failed
    - queue - (queue name)
    - task - (job name)
    - exception - (exception class name)
    ###### type:
    - timing
    
#### Component ResponseTimeComponent
```
AirSlate\Datadog\Components\ResponseTimeComponent
```
##### Metrics added by component:
- {application_namespace}.response_time
    ###### tags: 
    - code - (http response code)
    - method - (http request method)
    ###### type:
    - timing

#### Component CustomEventsComponent
```
AirSlate\Datadog\Components\CustomEventsComponent
```
##### Description
- To send simple metric you must implement 
```AirSlate\Datadog\Events\DatadogEventInterface```
- To send metric with specific type you must implement 
```AirSlate\Datadog\Events\DatadogEventExtendedInterface```

 Supported metric types: 
  -     DatadogEventExtendedInterface::METRIC_TYPE_HISTOGRAM
  -     DatadogEventExtendedInterface::METRIC_TYPE_INCREMENT
  -     DatadogEventExtendedInterface::METRIC_TYPE_DECREMENT
  -     DatadogEventExtendedInterface::METRIC_TYPE_GAUGE
  -     DatadogEventExtendedInterface::METRIC_TYPE_TIMING
##### Metrics added by component:
- {application_namespace}.{$event->getEventCategory()}.{$event->getEventName()}
    ###### tags: 
    - code - (http response code)
    - method - (http request method)
    ###### type:
    - timing
