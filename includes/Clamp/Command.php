<?php

namespace Clamp;

use ConsoleKit;

abstract class Command extends ConsoleKit\Command
{
    protected $separator = ' ';

    protected $parameter = '%1$s=%2$s';

    public function getConfig($expr = null)
    {
        return $this->getConsole()->getOptionsParser()->getConfig($expr);
    }

    protected function buildParameters($options = array())
    {
        $parameters = array();
        $options = $this->flatten($options);

        // Filter if needed.
        $subset = func_get_args();
        array_shift($subset);

        if (!empty($subset)) {
            $options = array_intersect_key($options, array_flip($subset));
        }

        foreach ($options as $key => $value) {
            $parameters[$key] = sprintf($this->parameter, $key, $value);
        }

        return implode($this->separator, $parameters);
    }

    public function getPath($option)
    {
        preg_match('@\'(.*?)\'@i', $option, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }
    }

    public function getPaths($options)
    {
        $paths = array();

        foreach ($options as $key => $value) {
            if (is_string($value) && $path = $this->getPath($value)) {
                $paths[$key] = $path;
            }
        }

        return $paths;
    }

    public function preparePaths($options)
    {
        $paths = $this->getPaths($options);

        foreach ($paths as $path) {
            if ($extension = pathinfo($path, PATHINFO_EXTENSION)) {
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                if ($extension == 'log' && !file_exists($path)) {
                    file_put_contents($path, '');
                }
            }
            else if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }

        return $paths;
    }

    protected function flatten($options, $parent = null)
    {
        $flatened = array();

        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $flatened = array_replace($flatened, $this->flatten($value, $key));
            }
            else {
                $key = $parent ? $parent . ' ' . $key : $key;
                $flatened[$key] = $value;
            }
        }

        return $flatened;
    }

    protected function isRunning($pidFile)
    {
        if (file_exists($pidFile) && $pid = file_get_contents($pidFile)) {
            $pid = preg_replace('~[.[:cntrl:][:space:]]~', '', $pid);
            $count = preg_replace('~[.[:cntrl:][:space:]]~', '', shell_exec('ps aux | grep ' . $pid . ' | wc -l'));

            if ($count > 0) {
                return true;
            }
        }

        return false;
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
}