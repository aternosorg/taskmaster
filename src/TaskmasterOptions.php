<?php

namespace Aternos\Taskmaster;

class TaskmasterOptions
{
    protected const POSSIBLE_BOOTSTRAP_PATHS = [
        __DIR__ . "/../../../autoload.php", // installed as dependency
        __DIR__ . "/../vendor/autoload.php", // standalone
        "vendor/autoload.php" // working directory
    ];

    protected ?string $bootstrap = null;


    /**
     * @return string
     */
    public function getBootstrap(): string
    {
        if ($this->bootstrap === null) {
            $this->bootstrap = $this->autoDetectBootstrap();
        }
        if ($this->bootstrap === null) {
            throw new \RuntimeException("Could not find bootstrap file.");
        }
        return $this->bootstrap;
    }

    /**
     * @return string|null
     */
    protected function autoDetectBootstrap(): ?string
    {
        foreach (static::POSSIBLE_BOOTSTRAP_PATHS as $path) {
            if (file_exists($path)) {
                return realpath($path);
            }
        }
        return null;
    }

    /**
     * @param string|null $bootstrap
     * @return $this
     */
    public function setBootstrap(?string $bootstrap): static
    {
        if (file_exists($bootstrap)) {
            $this->bootstrap = realpath($bootstrap);
        }
        return $this;
    }
}