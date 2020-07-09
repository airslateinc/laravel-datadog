<?php

return [
    'application_namespace' => env('APP_NAMESPACE', 'airslate'),
    'application_name' => env('APP_NAME', 'unknown'),
    'statsd_server' => env('STATSD_HOST', '172.17.0.1'),
    'statsd_port' => env('STATSD_PORT', 8125),
    'statsd_env' => env('APP_ENV'),
    'is_send_increment_metric_with_timing_metric' => false,
    'global_tags' => [],
];
