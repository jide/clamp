<?php

namespace Clamp;

use ConsoleKit;

class ConfigOptionsParser extends ConsoleKit\DefaultOptionsParser implements ConsoleKit\OptionsParser
{
    protected $defaultsFile;

    protected $configFile;

    protected $config;

    protected $yaml = array();

    protected $variables = array();

    public function __construct()
    {
        $this->variables = array(
            '{{$cwd}}' => getcwd()
        );

        $version = substr(php_uname('r'), 0, 2);

        $this->defaultsFile = __DIR__ . '/../../clamp.defaults.' . $version . '.yaml';

        if (!file_exists($this->defaultsFile)) {
            $this->defaultsFile = __DIR__ . '/../../clamp.defaults.yaml';
        }

        $this->configFile = 'clamp.yaml';
    }

    public function parse(array $argv)
    {
        if (!isset($this->config)) {
            $this->parseConfigFile();
        }

        list($args, $options) = parent::parse($argv);

        $type = isset($args[0]) ? $args[0] : null;

        if (isset($this->config[$type])) {
            $config = $this->config[$type];
            $options = array_replace_recursive($config['options'], $options);
        }

        return array($args, $options);
    }

    public function setDefaultsFile($defaultsFile)
    {
        $this->defaultsFile = $defaultsFile;
        return $this;
    }

    public function getDefaultsFile()
    {
        return $this->defaultsFile;
    }

    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
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

    public function getConfig($expr = null)
    {
        if (!isset($expr)) {
            return $this->config;
        }

        $expr = substr($expr, 2);
        $value = $this->arrayGet($this->config, $expr);

        if (is_array($value) && count($value) == 1) {
            $value = reset($value);
        }

        return $value;
    }

    public function setVariables($variables)
    {
        $this->variables = $variables;
        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    protected function parseConfigFile()
    {
        $this->yaml = array();
        $files = array($this->defaultsFile, $this->configFile);

        foreach ($files as $file) {
            if (file_exists($file)) {
                $yaml = \Spyc::YAMLLoad($file);
                if (!$yaml) {
                    throw new ConsoleKit\ConsoleException("Error parsing '$file'");
                }

                $this->yaml = array_replace_recursive($this->yaml, $yaml);
            }
        }

        $this->config = $this->parseVariable();

        return $this;
    }

    protected function parseVariable($yaml = null)
    {
        $yaml = ($yaml ? $yaml : $this->yaml);

        foreach ($yaml as $key => &$value) {
            if (is_array($value)) {
                $value = $this->parseVariable($value);
            }

            while (is_string($value) and false !== strpos($value, '{{')) {
                if (false !== strpos($value, '{{$.')) {
                    $value = substr($value, 4, -2);
                    $newValue = $this->arrayGet($this->yaml, $value);
                } else {
                    $newValue = str_replace(
                        array_keys($this->variables),
                        array_values($this->variables),
                        $value
                    );
                }

                if (isset($newValue) and $value != $newValue) {
                    $value = $newValue;
                }
            }

            if (is_array($value)) {
                $value = $this->parseVariable($value);
            }
        }

        return $yaml;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    protected function arrayGet($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }
}
