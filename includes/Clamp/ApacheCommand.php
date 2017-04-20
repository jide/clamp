<?php

namespace Clamp;

use ConsoleKit;

class ApacheCommand extends \Clamp\Command
{
    protected $parameter = '-c "%1$s %2$s"';
    protected $autoopen = FALSE;
    protected $servername = null;

    public function executeStart(array $args = array(), array $options = array())
    {
        $this->_configureAutoopen($options);
        if (!$this->isRunning($this->getPath($options['pidfile']))) {
            unset($options['lockfile']);
            $this->preparePaths($options);
            exec($this->getConfig('$.apache.commands.httpd') . ' -f /dev/null ' . $this->buildParameters($options) . ' > /dev/null &');
            $this->waitFor($this->getPath($options['pidfile']));
            $this->writeln('Apache server started', ConsoleKit\Colors::GREEN);
        }
        else {
            $this->writeln('Apache server is already running', ConsoleKit\Colors::YELLOW);
        }

        $this->_openInBrowser();
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

    /**
     * Checks conditions for and configures autoopen feature.
     *
     * Autoopen feature allows the script to open the url served by apache
     * in the default browser of the calling user.
     * Autoopen feature is enabled if 'autoopen' is activated in the
     * configuration and there exists a nonempty servername which is not a
     * star.
     *
     * @since: 1.3.4
     * @param array &$options The parsed json configuration options.
     * @return: This method does not return anything.
     */
    protected function _configureAutoopen(array &$options)
    {
        if (!isset($options['autoopen']))
        {
            return;
        }

        $hasServername = isset($options['servername']) && $options['servername'];
        $this->autoopen = $options['autoopen'] === TRUE && $hasServername;
        $this->servername = $hasServername ? $options['servername'] : null;
        unset($options['autoopen']);
    }

    /**
     * Opens the URL constructed by the servername in the browser or logs
     * how to do that.
     *
     * The URL is constructed by prepending 'http://' before the servername.
     * This method makes use of the 'open' command of MacOS to open the URL in
     * the default browser of the current user.
     *
     * @since: 1.3.4
     * @return: This method does not return anything.
     */
    protected function _openInBrowser()
    {
        if ($this->autoopen)
        {
            $urlToOpen = 'http://'.$this->servername;
            $this->writeln('Opening ' . $this->servername . ' in Browser.', ConsoleKit\Colors::GREEN);
            exec('open http://' . $this->servername);
        }
        else {
            $this->writeln('You can browse to ' . $this->servername . ' to see your site.' , ConsoleKit\Colors::GREEN);
        }
    }
}
