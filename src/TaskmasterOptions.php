<?php

namespace Aternos\Taskmaster;

use RuntimeException;

/**
 * Class TaskmasterOptions
 *
 * This class holds all global options for Taskmaster, passed around
 * workers, proxies etc. and therefore has to be serializable.
 *
 * @package Aternos\Taskmaster
 */
class TaskmasterOptions
{
    /**
     * Possible paths to the bootstrap file, used to auto-detect the bootstrap file
     * if no bootstrap file is defined, see {@link TaskmasterOptions::autoDetectBootstrap()}
     *
     * @var string[]
     */
    protected const POSSIBLE_BOOTSTRAP_PATHS = [
        __DIR__ . "/../../../autoload.php", // installed as dependency
        __DIR__ . "/../vendor/autoload.php", // standalone
        "vendor/autoload.php" // working directory
    ];

    protected ?string $bootstrap = null;
    protected string $phpExecutable = "php";

    /**
     * Get the path to the bootstrap file
     *
     * If no bootstrap file is defined, the bootstrap file will be auto-detected,
     * see {@link TaskmasterOptions::autoDetectBootstrap()}
     *
     * @return string
     */
    public function getBootstrap(): string
    {
        if ($this->bootstrap === null) {
            $this->bootstrap = $this->autoDetectBootstrap();
        }
        if ($this->bootstrap === null) {
            throw new RuntimeException("Could not find bootstrap file.");
        }
        return $this->bootstrap;
    }

    /**
     * Auto-detect the bootstrap file
     *
     * Tries to find the bootstrap file from the paths defined in {@link TaskmasterOptions::POSSIBLE_BOOTSTRAP_PATHS}
     *
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
     * Set the path to the bootstrap file
     *
     * If the bootstrap file does not exist, it will not be set.
     * If no bootstrap file is defined, the bootstrap file will be auto-detected,
     * see {@link TaskmasterOptions::autoDetectBootstrap()}
     *
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

    /**
     * Get the path to the PHP executable
     *
     * @return string
     */
    public function getPhpExecutable(): string
    {
        return $this->phpExecutable;
    }

    /**
     * Set the path to the PHP executable
     *
     * If no executable is set, just "php" is used which should follow the PATH environment variable.
     *
     * @param string $phpExecutable
     * @return $this
     */
    public function setPhpExecutable(string $phpExecutable): static
    {
        $this->phpExecutable = $phpExecutable;
        return $this;
    }
}