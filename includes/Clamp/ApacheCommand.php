<?php

namespace Clamp;

use ConsoleKit;

class ApacheCommand extends \Clamp\Command
{
    protected $parameter = '-c "%1$s %2$s"';

    public function executeStart(array $args = array(), array $options = array())
    {
        if (!$this->isRunning($this->getPath($options['pidfile']))) {
            unset($options['lockfile']);
            $this->preparePaths($options);
            exec($this->getConfig('$.apache.commands.httpd') . ' -f /dev/null ' . $this->buildParameters($options) . ' > /dev/null &');
            $this->waitFor($this->getPath($options['pidfile']));
            exec('sudo chmod g+rwx ' . $this->getPath($options['pidfile']));
            $this->writeln('Apache server started', ConsoleKit\Colors::GREEN);
        }
        else {
            $this->writeln('Apache server is already running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        if ($this->isRunning($this->getPath($options['pidfile']))) {
            exec('sudo kill -TERM $(cat ' . $options['pidfile'] . ')');
            $this->waitForNoMore($this->getPath($options['pidfile']));
            $this->writeln('Apache server stopped', ConsoleKit\Colors::RED);
        }
        else {
            $this->writeln('Apache server is not running', ConsoleKit\Colors::YELLOW);
        }
    }
}
