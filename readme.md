# Datadog middleware helper for laravel 

This project makes it simple to integrate Datadog into your.

## Requirements

- PHP >= 7.1
- Laravel Framework 5.6.*

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


### Publish client configuration:

```bash
php artisan vendor:publish --tag=datadog
```

### Add middleware. 
Datadog middleware must be last in your middleware list.

```php
$middleware = [
    // ...
    \AirSlate\Datadog\Http\Middleware\DatadogMiddlware::class,
];
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
