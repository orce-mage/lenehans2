<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportExportCore
 */


declare(strict_types=1);

namespace Amasty\ImportExportCore\Parallelization;

use Magento\Framework\App\ResourceConnection;

/**
 * phpcs:ignoreFile
 * @codeCoverageIgnore Can't be covered by unit/integration tests due to pcntl_fork() usage
 */
class JobManager
{
    const JOB_STATUS_DONE = 0;
    const JOB_STATUS_FAILED = 1;
    const JOB_STATUS_PROCESSING = 2;

    const DEFAULT_JOBS_LIMIT = 4;

    /**
     * @var array
     */
    private $allPids = [];

    /**
     * @var array
     */
    private $childrenSockets = [];

    /**
     * @var resource|null
     */
    private $parentSocket;

    /**
     * @var array
     */
    private $jobsInProgress = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $maxJobs;

    /**
     * @var callable
     */
    private $jobDoneCallback;

    public function __construct(
        ResourceConnection $resourceConnection,
        callable $jobDoneCallback = null,
        $maxJobs = self::DEFAULT_JOBS_LIMIT
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->maxJobs = $maxJobs;
        $this->jobDoneCallback = $jobDoneCallback;
    }

    public static function isAvailable(): bool
    {
        return function_exists('pcntl_fork');
    }

    public function fork(): int
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $pid = \pcntl_fork();
        if ($pid == -1) {
            throw new \RuntimeException('Could not fork a child process.');
        } elseif ($pid) {
            $this->allPids[] = $pid;
            $this->jobsInProgress[$pid] = $pid;
            $this->resourceConnection->closeConnection();
            $this->childrenSockets[$pid] = $sockets[0];
            fclose($sockets[1]);
        } else {
            $this->parentSocket = $sockets[1];
            fclose($sockets[0]);
        }

        return $pid;
    }

    public function getJobStatus(int $pid, bool $waitForTermination = false): int
    {
        switch (pcntl_waitpid($pid, $status, $waitForTermination ? 0 : WNOHANG)) {
            case $pid:
                $this->getChildReport($pid);
                return pcntl_wexitstatus($status) === 0 ? self::JOB_STATUS_DONE : self::JOB_STATUS_FAILED;
            case 0:
                return self::JOB_STATUS_PROCESSING;
            default:
                return self::JOB_STATUS_FAILED;
        }
    }

    public function waitForFreeSlot(): int
    {
        while (count($this->jobsInProgress) >= $this->maxJobs) {
            foreach ($this->jobsInProgress as $pid) {
                switch ($this->getJobStatus($pid)) {
                    case self::JOB_STATUS_DONE:
                        unset($this->jobsInProgress[$pid]);

                        return $pid;
                    case self::JOB_STATUS_FAILED:
                        throw new \RuntimeException('One of workers processes had failed.');
                    default:
                        continue 2;
                }
            }

            sleep(1);
        }

        return 0;
    }

    public function waitForJobs(array $pids): \Generator
    {
        foreach ($pids as $pid) {
            if (pcntl_waitpid($pid, $status) === -1) {
                throw new \RuntimeException(
                    'Error while waiting for worker process; Status: ' . $status
                );
            }

            $this->getChildReport($pid);
            unset($this->jobsInProgress[$pid]);
            yield $pid;
        }
    }

    public function waitForJobCompletion(): \Generator
    {
        return $this->waitForJobs($this->jobsInProgress);
    }

    public function waitForAllJobs(): array
    {
        $result = [];
        foreach ($this->waitForJobCompletion() as $pid) {
            $result[] = $pid;
        }

        return $result;
    }

    /**
     * Protect against Zombie children
     */
    public function __destruct()
    {
        $this->waitForJobs($this->allPids);
    }

    public function reportToParent(string $message): JobManager
    {
        if ($this->parentSocket) {
            fwrite($this->parentSocket, $message);
        }

        return $this;
    }

    protected function getChildReport(int $pid): ?string
    {
        if (empty($this->childrenSockets[$pid])) {
            return null;
        }

        $report = fgets($this->childrenSockets[$pid]);
        if (is_callable($this->jobDoneCallback)) {
            call_user_func($this->jobDoneCallback, $report);
        }

        return $report ?: null;
    }
}
