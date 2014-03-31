<?php

namespace Clamp;

use ConsoleKit;

class Console extends ConsoleKit\Console
{
    protected $helpCommandClass = 'ConsoleKit\HelpCommand';
    
    protected $configFile;

    protected $config;

    public function __construct($configFile = 'clamp.json', array $commands = array(), OptionsParser $parser = null, TextWriter $writer = null)
    {
        $this->setConfigFile($configFile);

        parent::__construct($commands, $parser, $writer);
    }

    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;

        $this->config = array();

        if (file_exists($configFile)) {
            $json = file_get_contents($configFile);
            $this->config = json_decode($json, true);

            // Merge PHP config into Apache's.
            if (isset($this->config['php'])) {
                foreach ($this->config['php'] as $key => $value) {
                    $this->config['apache']['php_value ' . $key] = $value;
                }
                unset($this->config['php']);
            }
        }

        return $this;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig($type = null)
    {
        if (isset($type)) {
            if (isset($this->config[$type])) {
                return $this->config[$type];
            }
            else {
                return array();
            }
        }
        else {
            return $this->config;
        }
    }
}