<?php
declare(strict_types=1);

namespace AirSlate\Datadog\Services;

use Illuminate\Contracts\Queue\Job;

/**
 * Class QueueJobMeter
 *
 * @package AirSlate\Datadog\Services
 */
class QueueJobMeter
{
    /**
     * @var string
     */
    protected $previousId;

    /**
     * @var float
     */
    protected $jobStarted;

    /**
     * @param Job $job
     */
    public function start(Job $job): void
    {
        $this->jobStarted = microtime(true);
        $this->previousId = $job->getJobId();
    }

    /**
     * @param Job $job
     * @return int|float
     */
    public function stop(Job $job): float
    {
        if ($this->previousId === $job->getJobId()) {
            return microtime(true) - $this->jobStarted;
        } else {
            return floatval(0);
        }
    }
}
