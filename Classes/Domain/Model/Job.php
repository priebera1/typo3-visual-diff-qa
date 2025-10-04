<?php

declare(strict_types=1);

namespace Devsk\Visualdiff\Domain\Model;

/**
 * Job model for visual diff comparison
 */
class Job
{
    protected string $jobId = '';
    protected string $baseUrlA = '';
    protected string $baseUrlB = '';
    protected array $urls = [];
    protected float $threshold = 1.0;
    protected string $status = 'pending';
    protected ?\DateTime $createdAt = null;
    protected ?\DateTime $completedAt = null;
    protected array $results = [];

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }

    public function getBaseUrlA(): string
    {
        return $this->baseUrlA;
    }

    public function setBaseUrlA(string $baseUrlA): void
    {
        $this->baseUrlA = $baseUrlA;
    }

    public function getBaseUrlB(): string
    {
        return $this->baseUrlB;
    }

    public function setBaseUrlB(string $baseUrlB): void
    {
        $this->baseUrlB = $baseUrlB;
    }

    public function getUrls(): array
    {
        return $this->urls;
    }

    public function setUrls(array $urls): void
    {
        $this->urls = $urls;
    }

    public function getThreshold(): float
    {
        return $this->threshold;
    }

    public function setThreshold(float $threshold): void
    {
        $this->threshold = $threshold;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTime $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): void
    {
        $this->results = $results;
    }
}
