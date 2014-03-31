<?php

namespace Clamp;

use ConsoleKit;

class MysqlCommand extends \Clamp\Command
{
    protected static $binPath = '$(brew --prefix mariadb)/bin/';

    protected static $baseDir = '$(brew --prefix mariadb)/';

    public function executeStart(array $args = array(), array $options = array())
    {
        if (!file_exists($this->getPath('socket'))) {
            exec($this->getBinPath() . 'mysqld_safe --defaults-file=/dev/null ' . $this->getParameters() . ' > /dev/null &');
            $this->waitFor($this->getPath('socket'));
            $this->writeln('MySQL server started', ConsoleKit\Colors::GREEN);
        }
        else {
            $this->writeln('MySQL server is already running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        if (file_exists($this->getPath('socket'))) {
            exec($this->getBinPath() . 'mysqladmin -u root ' . $this->getParameter('socket') . ' shutdown');
            $this->waitForNoMore($this->getPath('socket'));
            $this->writeln('MySQL server stopped', ConsoleKit\Colors::RED);
        }
        else {
            $this->writeln('MySQL server is not running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeInstall(array $args = array(), array $options = array())
    {
        if (!file_exists($this->getPath('datadir') . '/mysql')) {
            exec($this->getBinPath() . 'mysql_install_db --user=`whoami` ' . $this->getParameters('basedir', 'datadir'));
            $this->waitFor($this->getPath('datadir') . '/mysql');
            $this->writeln('Initialized MySQL data directory', ConsoleKit\Colors::CYAN);
        }
        else {
            $this->writeln('MySQL data directory already exists', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeCreateDb(array $args = array(), array $options = array())
    {
        if ($this->getOption('db')) {
            if (!file_exists($this->getPath('datadir') . '/' . $this->getOption('db'))) {
                exec($this->getBinPath() . 'mysql -u `whoami` ' . $this->getParameter('socket') . ' -e "CREATE DATABASE IF NOT EXISTS ' . $this->getOption('db') . '"');
                $this->waitFor($this->getPath('datadir') . '/' . $this->getOption('db'));
                $this->writeln('Created database ' . $this->getOption('db'), ConsoleKit\Colors::CYAN);
            }
            else {
                $this->writeln('Database ' . $this->getOption('db') . ' already exists', ConsoleKit\Colors::YELLOW);
            }
        }
        else {
            $this->writeln('No database to create', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeExport(array $args = array(), array $options = array())
    {
        if (file_exists($this->getPath('socket'))) {
            exec($this->getBinPath() . 'mysqldump -u `whoami` ' . $this->getParameter('socket') . ' ' . $this->getOption('db') . ' > ' . $this->getOption('db') . '.sql');
            $this->waitFor($this->getOption('db') . '.sql');
            $this->writeln('Database ' . $this->getOption('db') . ' exported to ' . $this->getOption('db') . '.sql', ConsoleKit\Colors::CYAN);
        }
        else {
            $this->writeln('MySQL server is not running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeImport(array $args = array(), array $options = array())
    {
        if (file_exists($this->getPath('socket'))) {
            exec($this->getBinPath() . 'mysql -u `whoami` ' . $this->getParameter('socket') . ' ' . $this->getOption('db') . ' < ' . $this->getOption('db') . '.sql');
            $this->writeln('File ' . $this->getOption('db') . '.sql imported to ' . $this->getOption('db'), ConsoleKit\Colors::CYAN);
        }
        else {
            $this->writeln('MySQL server is not running', ConsoleKit\Colors::YELLOW);
        }
    }

    public function getDefaults()
    {
        $path = getcwd();

        return array(
            "bind-address" => "127.0.0.1",
            "port" => "3306",
            "basedir" => self::$baseDir,
            "datadir" => "'$path/.clamp/data'",
            "socket" => "'$path/.clamp/tmp/mysql.sock'",
            "pid-file" => "'$path/.clamp/tmp/mysql.pid'",
            "log_error" => "'$path/.clamp/logs/mysql.error.log'",
            "max_binlog_size" => "10M"
        );
    }

    protected function getParametrised($name, $option)
    {
        return "--$name=$option";
    }
}