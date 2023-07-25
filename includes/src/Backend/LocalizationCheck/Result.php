<?php declare(strict_types=1);

namespace JTL\Backend\LocalizationCheck;

use Illuminate\Support\Collection;

/**
 * Class Result
 * @package JTL\Backend\LocalizationCheck
 */
class Result
{
    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    private string $location;

    /**
     * @var Collection
     */
    private Collection $excessLocalizations;

    /**
     * @var Collection
     */
    private Collection $missingLocalizations;

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return Collection
     */
    public function getExcessLocalizations(): Collection
    {
        return $this->excessLocalizations;
    }

    /**
     * @param Collection $excessLocalizations
     */
    public function setExcessLocalizations(Collection $excessLocalizations): void
    {
        $this->excessLocalizations = $excessLocalizations;
    }

    /**
     * @return Collection
     */
    public function getMissingLocalizations(): Collection
    {
        return $this->missingLocalizations;
    }

    /**
     * @param Collection $missingLocalizations
     */
    public function setMissingLocalizations(Collection $missingLocalizations): void
    {
        $this->missingLocalizations = $missingLocalizations;
    }

    /**
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->missingLocalizations->count() + $this->excessLocalizations->count();
    }

    /**
     * @return bool
     */
    public function hasPassed(): bool
    {
        return $this->getErrorCount() === 0;
    }
}
