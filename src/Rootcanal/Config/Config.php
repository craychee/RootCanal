<?php

namespace Rootcanal\Config;
use Rootcanal\Config\ConfigLoader;
use Rootcanal\Config\ConfigTree;
use Symfony\Component\Config\Definition\Processor;

class Config
{
    protected $configuration = null;
    protected $productionMode;

    public function __construct($productionMode)
    {
        $this->productionMode   = $productionMode;
    }

    public function getConfig()
    {
        return $this->configuration ?: $this->setConfig();
    }

    public function getDestinationConfig()
    {
        $config = $this->getConfig();
        $destination = [];

        foreach ($config['destination_paths'] as $key => $config) {
            $destination[$key] = $this->getFullDestination() . DIRECTORY_SEPARATOR . $config;
        }

        // Set the custom destination off of the module destination.
        $destination['custom'] = $destination['module'] . DIRECTORY_SEPARATOR . '%s';

        return $destination;
    }

    public function getSourceConfig()
    {
        return $this->getConfig()['source_paths'];
    }

    public function getFinderConfig()
    {
        return $this->getConfig()['finder_settings'];
    }

    public function setConfig()
    {
        $config = $this->createConfigLoader();
        $processor = $this->createProcessor();
        $tree = $this->createConfigTree();

        $this->configuration = $processor->processConfiguration(
            $tree,
            $config->loadConfig()
        );

        return $this->configuration;
    }

    /**
     * Finds the path to configuration.
     */
    protected function getConfigPath()
    {
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR);
        $paths = array_filter(
            array(
                $cwd . DIRECTORY_SEPARATOR . 'drupal.yml',
                $cwd . DIRECTORY_SEPARATOR . 'drupal.yml.dist',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'drupal.yml',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'drupal.yml.dist',
                $cwd . DIRECTORY_SEPARATOR . 'cnf' . DIRECTORY_SEPARATOR . 'drupal.yml',
                $cwd . DIRECTORY_SEPARATOR . 'cnf' . DIRECTORY_SEPARATOR . 'drupal.yml.dist',
            ),
            'is_file'
        );

        if (count($paths)) {
            return current($paths);
        }

        return null;
    }

    /**
     * Creates configuration loader.
     *
     * @return ConfigurationLoader
     */
    protected function createConfigLoader()
    {
        return new ConfigLoader($this->getConfigPath());
    }

    /**
     * Creates configuration loader.
     *
     * @return ConfigurationLoader
     */
    protected function createConfigTree()
    {
        return new ConfigTree();
    }

    /**
     * Creates configuration loader.
     *
     * @return ConfigurationLoader
     */
    protected function createProcessor()
    {
        return new Processor();
    }

    public function isProductionEnabled()
    {
        return $this->productionMode;
    }

    public function getDestination()
    {
        return $this->getConfig()['destination_paths']['root'];
    }

    public function getFullDestination()
    {
        return implode(DIRECTORY_SEPARATOR, [getcwd(), rtrim($this->getDestination(), DIRECTORY_SEPARATOR)]);
    }

    public function getPaths()
    {
        $config = $this->getDestinationConfig();

        return [
            'core'          => $this->getFullDestination(),
            'module'        => $config['module'] . DIRECTORY_SEPARATOR . '%s',
            'custom'        => $config['custom'] . DIRECTORY_SEPARATOR . '%s',
            'theme'         => $config['theme']  . DIRECTORY_SEPARATOR . '%s',
            'profile'       => $config['profile']. DIRECTORY_SEPARATOR . '%s',
            'drush'         => $config['drush'],
            'files-public'  => $config['files_public'],
            'files-private' => $config['files_private'],
            'settings'      => $config['settings'],
            'vendor'        => $config['vendor'],
            ];
    }

    public function getPathsByType($type)
    {
        return $this->getPaths()[$type];
    }
}
