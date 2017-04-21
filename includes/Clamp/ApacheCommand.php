<?php

namespace Clamp;

use ConsoleKit;

class ApacheCommand extends \Clamp\Command
{
    protected $parameter = '-c "%1$s %2$s"';
    protected $autoopen = FALSE;
    protected $servername = null;
    protected $port = "80";
    protected $useSSL = FALSE;

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
     * Autoopen feature is enabled if first 'autoopen' is activated in the
     * configuration then second there exists a nonempty servername which is
     * not a star and last but not least there is a port number given with
     * 'listen' option.
     *
     * @since: 1.4.0
     * @version: 1.4.1
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
        $hasPortnumber = isset($options['listen']) && $options['listen'];
        $this->autoopen = $options['autoopen'] === TRUE && $hasServername;
        $this->servername = $hasServername ? $options['servername'] : null;
        $this->port = $hasServername ? $options['listen'] : null;
        $this->useSSL = isset($options['SSLEngine']) && strtolower($options['SSLEngine']) === "on";
        unset($options['autoopen']);
    }

    /**
     * Opens the URL constructed by the servername in the browser or logs
     * how to do that.
     *
     * The URL is constructed by prepending 'http://' or 'https://'
     * (if 'SSLEngine' is set to 'On' in apache options) before the servername
     * and appending the port set in 'listen' option of apache options.
     * This method makes use of the 'open' command of MacOS to open the URL in
     * the default browser of the current user.
     *
     * @since: 1.4.0
     * @version: 1.4.1
     * @return: This method does not return anything.
     */
    protected function _openInBrowser()
    {
        $urlToOpen = $this->_buildURL();
        if ($this->autoopen)
        {
            $this->writeln('Opening ' . $urlToOpen . ' in Browser.', ConsoleKit\Colors::GREEN);
            exec('open ' . $urlToOpen);
        }
        else {
            $this->writeln('You can browse to ' . $urlToOpen . ' to see your site.' , ConsoleKit\Colors::GREEN);
        }
    }

    /**
     * Build the URL to open out of the configuration given by instance
     * variables.
     *
     * @since: 1.4.1
     * @version: 1.4.1
     * @return: Returns the url on which the site can be reached.
     */
    protected function _buildURL()
    {
        $protocol = $this->useSSL ? 'https://' : 'http://';
        $port = $this->port !== "80" ? ':'.$this->port : '';
        return $protocol.$this->servername.$port;
    }
}
