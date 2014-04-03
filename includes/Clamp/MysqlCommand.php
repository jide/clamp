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
                exec($this->getConfig('$.mysql.command.mysqld') . '  --defaults-file=/dev/null ' . $this->buildParameters($options) . ' > /dev/null &');
                $this->waitFor($this->getPath($options['socket']));
                $this->writeln('MySQL server started', ConsoleKit\Colors::GREEN);
            }
            else {
                $this->writeln('MySQL server is already running', ConsoleKit\Colors::YELLOW);
            }
        }
        else if (!empty($args) && $args[0] == 'stop') {
            if ($this->isRunning($this->getPath($options['pid-file']))) {
                exec($this->getConfig('$.mysql.command.mysqladmin') . ' --user=root ' . $this->buildParameters($options, 'socket') . ' shutdown');
                $this->waitForNoMore($this->getPath($options['socket']));
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
            exec($this->getConfig('$.mysql.command.mysql_install_db') . ' --user=`whoami` ' . $this->buildParameters($options, 'basedir', 'datadir'));
            $this->waitFor($this->getPath($options['datadir']) . '/mysql');
            $this->writeln('Initialized MySQL data directory', ConsoleKit\Colors::CYAN);
        }
        else {
            $this->writeln('MySQL data directory already exists', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeCreateDb(array $args = array(), array $options = array())
    {
        list($db, ) = $this->getDb($args);

        if ($db) {
            if (!file_exists($this->getPath($options['datadir']) . '/' . $db)) {
                // Start server if needed.
                if (!$running = $this->isRunning($this->getPath($options['pid-file']))) {
                    $this->getConsole()->execute('mysql', array('install'), $options);
                    $this->getConsole()->execute('mysql', array('daemon', 'start'), $options);
                }

                exec($this->getConfig('$.mysql.command.mysql') . ' --user=root ' . $this->buildParameters($options, 'socket') . ' -e "CREATE DATABASE IF NOT EXISTS ' . $db . '"');
                $this->waitFor($this->getPath($options['datadir']) . '/' . $db);
                $this->writeln('Created database ' . $db, ConsoleKit\Colors::CYAN);

                if (!$running) {
                    $this->getConsole()->execute('mysql', array('daemon', 'stop'), $options);
                }
            }
            else {
                $this->writeln('Database ' . $db . ' already exists', ConsoleKit\Colors::YELLOW);
            }
        }
        else {
            $this->writeln('No database name set', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeExport(array $args = array(), array $options = array())
    {
        list($db, $file) = $this->getDb($args);

        if (empty($db)) {
            $db = '--all-databases';
        }

        $confirm = true;

        if (file_exists($file) && !strstr($file, '.backup')) {
            $confirm = false;
            $dialog = new ConsoleKit\Widgets\Dialog($this->getConsole());

            if ($dialog->confirm('File ' . $file . ' exists. Overwrite ?')) {
                $confirm = true;
            }
        }

        if ($confirm) {
            // Start server if needed.
            if (!$running = $this->isRunning($this->getPath($options['pid-file']))) {
                $this->getConsole()->execute('mysql', array('start'), $options);
            }

            exec($this->getConfig('$.mysql.command.mysqldump') . ' --user=root ' . $this->buildParameters($options, 'socket') . ' ' . $db . ' > ' . $file);
            $this->waitFor($file);
            $this->writeln('Database ' . $db . ' exported to ' . $file, ConsoleKit\Colors::GREEN);

            if (!$running) {
                $this->getConsole()->execute('mysql', array('stop'), $options);
            }
        }
    }

    public function executeImport(array $args = array(), array $options = array())
    {
        $args = array_reverse($args);
        if (count($args) == 1) {
            $args = array(null, $args[0]);
        }
        list($db, $file) = $this->getDb($args);

        if (!file_exists($file)) {
            $this->writeln('File ' . $file . ' does not exist', ConsoleKit\Colors::YELLOW);
        }
        else if (empty($db)) {
            $this->writeln('No database set', ConsoleKit\Colors::YELLOW);
        }
        else {
            $dialog = new ConsoleKit\Widgets\Dialog($this->getConsole());

            if ($dialog->confirm('All data in ' . $db . ' will be replaced with ' . $file . '. Import ?')) {
                // Start server if needed.
                if (!$running = $this->isRunning($this->getPath($options['pid-file']))) {
                    $$this->getConsole()->execute('mysql', array('start'), $options);
                }

                // Backup if needed.
                if ($this->getConfig('$.mysql.backup-on-import')) {
                    $this->getConsole()->execute('mysql', array('export', $db, $file . '.backup'), $options);
                }

                exec($this->getConfig('$.mysql.command.mysql') . ' --user=root ' . $this->buildParameters($options, 'socket') . ' ' . $db . ' < ' . $file);
                $this->writeln('File ' . $file . ' imported to ' . $db, ConsoleKit\Colors::GREEN);

                if (!$running) {
                    $this->getConsole()->execute('mysql', array('stop'), $options);
                }
            }
        }
    }

    protected function getDb($args = array())
    {
        if (!empty($args[0])) {
            $db = $args[0];
        }
        else {
            $db = $this->getConfig('$.mysql.db');
        }

        if (!empty($args[1])) {
            $file = $args[1];
        }
        else {
            $file = $this->getConfig('$.mysql.dump');
        }

        if (empty($file)) {
            if (!empty($db)) {
                $file = $db . '.sql';
            }
            else {
                $file = 'dump.sql';
            }
        }

        return array($db, $file);
    }
}