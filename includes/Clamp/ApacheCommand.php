<?php

namespace Clamp;

use ConsoleKit;

class ApacheCommand extends \Clamp\Command
{
    protected $parameter = '%1$s "%2$s"';
    protected $autoopen = false;
    protected $servername = null;
    protected $port = "80";
    protected $useSSL = false;
    protected $configureFile = '/./.clamp/tmp/httpd.conf';

    public function executeStart(array $args = array(), array $options = array())
    {
        $this->_configureAutoopen($options);

        $pid = array_get($options, 'pidfile');
        if ($this->isRunning($this->getPath($pid))) {
            $this->writeln('Apache server is already running', ConsoleKit\Colors::YELLOW);
        } else {
            $this->preparePaths($options);
            $file = $this->buildConfigureFile(array_get($options, 'conf'));
            exec($this->getConfig('$.apache.commands.httpd') . $this->buildParameters(['-f' => $file]) . ' > /dev/null &');
            $this->waitFor($this->getPath($pid));
            $this->writeln('Apache server started', ConsoleKit\Colors::GREEN);
        }

        $this->_openInBrowser();
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        $pid = array_get($options, 'pidfile');
        if ($this->isRunning($this->getPath($pid))) {
            exec('sudo kill -TERM $(cat ' . $pid . ')');
            $this->waitForNoMore($this->getPath($pid));
            $this->writeln('Apache server stopped', ConsoleKit\Colors::RED);
        } else {
            $this->writeln('Apache server is not running', ConsoleKit\Colors::YELLOW);
        }
    }

    protected function buildConfigureFile($confgiure)
    {
        $file = getcwd() . $this->configureFile;
        file_put_contents($file, $confgiure);
        return $file;
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
    protected function _configureAutoopen(array $options)
    {
        if (!isset($options['autoopen'])) {
            return;
        }

        $hasServername = isset($options['servername']) && $options['servername'];
        $hasPortnumber = isset($options['listen']) && $options['listen'];
        $this->autoopen = $options['autoopen'] === true && $hasServername;
        $this->servername = $hasServername ? $options['servername'] : null;
        $this->port = $hasServername ? $options['listen'] : null;
        $this->useSSL = isset($options['ssl']) && $options['ssl'];
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
        if ($this->autoopen) {
            $this->writeln('Opening ' . $urlToOpen . ' in Browser.', ConsoleKit\Colors::GREEN);
            exec('open ' . $urlToOpen);
        } else {
            $this->writeln('You can browse to ' . $urlToOpen . ' to see your site.', ConsoleKit\Colors::GREEN);
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
