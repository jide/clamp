<?php

namespace Clamp;

use ConsoleKit;

class MysqlCommand extends \Clamp\Command
{
    protected $parameter = '--%1$s=%2$s';

    public function executeStart(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('mysql', array('install'), $options);
        $this->getConsole()->execute('mysql', array('daemon', 'start'), $options);
        $this->getConsole()->execute('mysql', array('create-db'), $options);
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('mysql', array('daemon', 'stop'), $options);
    }

    public function executeDaemon(array $args = array(), array $options = array())
    {
        if (empty($args) || $args[0] == 'start') {
            if (!$this->isRunning($this->getPath($options['pid-file']))) {
                $this->preparePaths($options);
                exec($this->getConfig('$.mysql.commands.mysqld') . ' --defaults-file=/dev/null ' . $this->buildParameters($options) . ' &');
                $this->waitFor($this->getPath($options['pid-file']));
                $this->waitFor($this->getPath($options['socket']));
                $this->writeln('MySQL server started', ConsoleKit\Colors::GREEN);
            }
            else {
                $this->writeln('MySQL server is already running', ConsoleKit\Colors::YELLOW);
            }
        }
        else if (!empty($args) && $args[0] == 'stop') {
            if ($this->isRunning($this->getPath($options['pid-file']))) {
                exec($this->getConfig('$.mysql.commands.mysqladmin') . ' --user=root --password="' . $this->getConfig('$.mysql.users.root.password') . '" ' . $this->buildParameters($options, 'socket') . ' shutdown');
                $this->waitForNoMore($this->getPath($options['pid-file']));
                $this->writeln('MySQL server stopped', ConsoleKit\Colors::RED);
            }
            else {
                $this->writeln('MySQL server is not running', ConsoleKit\Colors::YELLOW);
            }
        }
    }

    public function executeInstall(array $args = array(), array $options = array())
    {
        if (!file_exists($this->getPath($options['datadir']) . '/mysql')) {
            $this->preparePaths($options);
            exec($this->getConfig('$.mysql.commands.mysql_install_db') . ' ' . $this->buildParameters($options, 'basedir', 'datadir'));
            $this->waitFor($this->getPath($options['datadir']) . '/mysql');
            $this->writeln('Initialized MySQL data directory', ConsoleKit\Colors::CYAN);
        }
        else {
            if ($this->verbose) $this->writeln('MySQL data directory already exists', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeCreateDb(array $args = array(), array $options = array())
    {
        $databases = empty($args[0]) ? $this->getConfig('$.mysql.databases') : array($args[0]);

        if (!empty($databases[0])) {
            foreach ($databases as $key => $database) {
                if (!file_exists($this->getPath($options['datadir']) . '/' . $database)) {
                    exec($this->getConfig('$.mysql.commands.mysql') . ' --user=root --password="' . $this->getConfig('$.mysql.users.root.password') . '" ' . $this->buildParameters($options, 'socket') . ' --execute="CREATE DATABASE IF NOT EXISTS ' . $database . '"');
                    $this->waitFor($this->getPath($options['datadir']) . '/' . $database);
                    $this->writeln('Created database ' . $database, ConsoleKit\Colors::CYAN);
                }
                else {
                    if ($this->verbose) $this->writeln('Database ' . $database . ' already exists', ConsoleKit\Colors::YELLOW);
                }
            }
        }
        else {
            if ($this->verbose) $this->writeln('No database name set', ConsoleKit\Colors::YELLOW);
        }
    }
}