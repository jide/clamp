<?php

namespace Clamp;

use ConsoleKit;

abstract class Command extends ConsoleKit\Command
{
    protected static $binPath = '';

    protected $options = array();

    public function execute(array $args, array $options = array())
    {
        exec('sudo -v');
        
        $this->options = $options;

        parent::execute($args, $this->options);
    }

    public function getBinPath()
    {
        return self::$binPath;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        // Merge defaults and config.
        $type = strtolower(substr(get_class($this), 6, -7));
        $config = array_replace($this->getDefaults(), $this->getConsole()->getConfig($type));

        // Filter if needed.
        $subset = func_get_args();

        if (!empty($subset)) {
            $config = array_intersect_key($config, array_flip($subset));
        }

        // Add runtime options.
        $options = array_replace($config, $this->options);

        $this->createFiles($options);

        return $options;
    }

    public function getOption($option)
    {
        $options = $this->getOptions($option);

        if (!empty($options)) {
            return reset($this->getOptions($option));
        }
        else {
            return null;
        }
    }

    public function getParameters()
    {
        $parameters = array();

        $options = $this->flatten(call_user_func_array(array($this, 'getOptions'), func_get_args()));

        foreach ($options as $name => $option) {
            $parameters[$name] = $this->getParametrised($name, $option);
        }

        return implode($this->getSeparator(), $parameters);
    }

    public function getParameter($option)
    {
        return $this->getParameters($option);
    }

    public function getDefaults()
    {
        return array();
    }

    public function getPath($name)
    {
        preg_match('@\'(.*?)\'@i', $this->getOption($name), $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }
    }

    protected function createFiles($options)
    {
        foreach (array('tmp', 'data', 'logs') as $dir) {
            if (!file_exists('.clamp/' . $dir)) {
                mkdir('.clamp/' . $dir, 0755, true);
            }
        }

        foreach ($options as $name => $option) {
            if (is_string($option) && preg_match('@\'(.*?)\'@i', $option, $matches)) {
                $file = $matches[1];
                if (strstr($file, '.log')) {
                    if (!file_exists(dirname($file))) mkdir(dirname($file), 0755, true);
                    if (!file_exists($file)) file_put_contents($file, '');
                }
            }
        }
    }

    protected function waitFor($file)
    {
        while (!file_exists($file)) {
            sleep(1);
        }
    }

    protected function waitForNoMore($file)
    {
        while (file_exists($file)) {
            sleep(1);
        }
    }

    protected function flatten($options, $parent = null)
    {
        $flat = array();

        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $flat = array_replace($flat, $this->flatten($value, $key));
            }
            else {
                $key = $parent ? $parent . ' ' . $key : $key;
                $flat[$key] = $value;
            }
        }

        return $flat;
    }

    protected function getParametrised($name, $option)
    {
        return  "$name=$option";
    }

    protected function getSeparator()
    {
        return  " ";
    }
}