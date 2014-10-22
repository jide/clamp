<?php

namespace Clamp;

use ConsoleKit;

class ConfigOptionsParser extends ConsoleKit\DefaultOptionsParser implements ConsoleKit\OptionsParser 
{
    protected $defaultsFile;

    protected $configFile;

    protected $config;

    protected $json = array();

    protected $variables = array();

    public function __construct()
    {
        $this->variables = array(
            'cwd' => getcwd()
        );

        $version = substr(php_uname('r'), 0, 2);
        $this->defaultsFile = __DIR__ . '/../../clamp.defaults.' . $version . '.json';

        if (!file_exists($this->defaultsFile)) {
            $this->defaultsFile = __DIR__ . '/../../clamp.defaults.json';
        }

        $this->configFile = 'clamp.json';
    }

    public function parse(array $argv)
    {
        if (!isset($this->config)) {
            $this->parseConfigFile();
        }

        list($args, $options) = parent::parse($argv);

        $type = $args[0];

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
        if (isset($expr)) {
            $value = jsonPath($this->config, $expr);

            if (is_array($value) && count($value) == 1) {
                return reset($value);
            }
            else {
                return $value;
            }
        }
        else {
            return $this->config;
        }
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
        $this->json = array();
        $files = array(__DIR__ . '/../../clamp.defaults.json', $this->configFile);

        foreach ($files as $file) {
            if (file_exists($file)) {
                $json = json_decode(file_get_contents($file), true);

                if (!$json) {
                    throw new ConsoleKit\ConsoleException("Error parsing '$file'");
                }
                else {
                    $this->json = array_replace_recursive($this->json, $json);
                }
            }
        }

        $this->config = $this->parseJson();

        return $this;
    }

    protected function parseJson($json = null)
    {
        $json = ($json ? $json : $this->json);

        foreach ($json as $key => &$value) {
            if (is_array($value)) {
                $value = $this->parseJson($value);
            }
            else {
                while (!is_array($value) && strstr($value, '{{')) {
                    // Variables.
                    if (preg_match('/\{\{\$[a-z\-_]+\}\}/', $value, $matches)) {
                        $newValue = preg_replace_callback('/\{\{\$([a-z\-_]+)\}\}/', array($this, 'replaceVariables'), $value);
                    }
                    // Pure JsonPath, could return an array.
                    else if (preg_match('/^\{\{(\$\.[a-z\-\._]+)\}\}$/', $value, $matches)) {
                        $newValue = $this->replaceJsonPath($matches);
                    }
                    // Inline JsonPath, returns a string.
                    else {
                        $newValue = preg_replace_callback('/\{\{(\$\.[a-z\-\._]+)\}\}/', array($this, 'replaceJsonPath'), $value);
                    }

                    if ($value != $newValue) {
                        $value = $newValue;
                    }
                    else {
                        break;
                    }
                }

                if (is_array($value)) {
                    $value = $this->parseJson($value);
                }
            }
        }

        return $json;
    }

    protected function replaceVariables($matches)
    {
        if (isset($this->variables[$matches[1]])) {
            return $this->variables[$matches[1]];
        }
    }

    protected function replaceJsonPath($matches)
    {
        $value = jsonPath($this->json, $matches[1]);
        return (is_array($value) && count($value) == 1 ? reset($value) : $value);
    }
}