<?php

namespace Clamp;

use ConsoleKit;

class ApacheCommand extends \Clamp\Command
{
    protected static $binPath = '/usr/sbin/';

    public function executeStart(array $args = array(), array $options = array())
    {
        if (!file_exists($this->getPath('pidfile'))) {
            exec('sudo ' . $this->getBinPath() . 'httpd -d . -f /dev/null ' . $this->getParameters() . ' > /dev/null &');
            $this->waitFor($this->getPath('pidfile'));
            $this->writeln('Apache server started', ConsoleKit\Colors::GREEN);
        }
        else {
            $this->writeln('Apache server is already running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        if (file_exists($this->getPath('pidfile'))) {
            exec('sudo kill -TERM `cat ' . $this->getOption('pidfile') . '`');
            $this->waitForNoMore($this->getPath('pidfile'));
            $this->writeln('Apache server stopped', ConsoleKit\Colors::RED);
        }
        else {
            $this->writeln('Apache server is already running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function getDefaults()
    {
        $path = getcwd();

        return array(
            "servername" => "localhost",
            "listen" => "80",
            "documentroot" => "'$path'",
            "serverroot" => "'$path'",
            "pidfile" => "'$path/.clamp/tmp/httpd.pid'",
            "lockfile" => "'$path/.clamp/tmp/accept.lock'",
            "loglevel" => "info",
            "errorlog" => "'$path/.clamp/logs/apache.error.log'",
            "customlog" => "'$path/.clamp/logs/apache.access.log' common",
            "addtype" => "application/x-httpd-php .php",
            "directoryindex" => "index.html index.php",
            "setenv" => "LOCAL_SERVER true",
            "user" => "`whoami`",
            "group" => "staff",
            "loadmodule" => array(
                "authz_host_module" => "/usr/libexec/apache2/mod_authz_host.so",
                "dir_module" => "/usr/libexec/apache2/mod_dir.so",
                "env_module" => "/usr/libexec/apache2/mod_env.so",
                "mime_module" => "/usr/libexec/apache2/mod_mime.so",
                "log_config_module" => "/usr/libexec/apache2/mod_log_config.so",
                "rewrite_module" => "/usr/libexec/apache2/mod_rewrite.so",
                "php5_module" => "/usr/libexec/apache2/libphp5.so"
            ),
            "php_value memory_limit" => "256M"
        );
    }

    protected function getParametrised($name, $option)
    {
        return "-c \"$name $option\"";
    }
}