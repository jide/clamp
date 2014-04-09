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
        $this->getConsole()->execute('mysql', array('root-password'), $options);
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

    public function executeRootPassword(array $args = array(), array $options = array())
    {
        $password = isset($args[0]) ? $args[0] : $this->getConfig('$.mysql.users.root.password');

        // Set root password.
        if ($password) {
            exec($this->getConfig('$.mysql.commands.mysql') . ' --user=root ' . $this->buildParameters($options, 'socket') . ' --execute="SET PASSWORD FOR \'root\'@\'localhost\' = PASSWORD(\'' . $password . '\')" 2>&1');
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

    public function executeExport(array $args = array(), array $options = array())
    {
        if (isset($args[0]) && !isset($args[1])) {
            $databases = array($args[0] => $args[0] . '.sql');
        }
        else if (isset($args[0]) && isset($args[1])) {
            $databases = array($args[0] => $args[1]);
        }
        else {
            $databases = $this->getConfig('$.mysql.databases');

            foreach ($databases as $key => $database) {
                $databases[$database] = $database . '.sql';
                unset($databases[$key]);
            }
        }

        if (empty($databases)) {
            $databases = array('--all-databases' => 'dump.sql');
        }

        foreach ($databases as $database => $file) {
            $confirm = true;

            if (file_exists($file) && !strstr($file, '.backup')) {
                $confirm = false;
                $dialog = new ConsoleKit\Widgets\Dialog($this->getConsole());
                $confirm = $dialog->confirm('File ' . $file . ' exists. Overwrite ?');
            }

            if ($confirm) {
                exec($this->getConfig('$.mysql.commands.mysqldump') . ' --user=root --password="' . $this->getConfig('$.mysql.users.root.password') . '" ' . $this->buildParameters($options, 'socket') . ' ' . $database . ' > ' . $file);
                $this->waitFor($file);
                $this->writeln('Database ' . $database . ' exported to ' . $file, ConsoleKit\Colors::GREEN);
            }
        }
    }

    public function executeImport(array $args = array(), array $options = array())
    {
        $databases = $this->getConfig('$.mysql.databases');

        if (isset($args[0]) && !isset($args[1]) && !empty($databases)) {
            if (count($databases) == 1) {
                $databases = array($databases[0] => $args[0]);
            }
            else {
                $dialog = new ConsoleKit\Widgets\Dialog($this->getConsole());
                $choice = $dialog->ask("Choose a database :\n- " . implode("\n- ", $databases) . "\nEnter a database name:", $databases[0]);
                $databases = array($choice => $args[0]);
            }
        }
        else if (isset($args[0]) && isset($args[1])) {
            $databases = array($args[1] => $args[0]);
        }
        else {
            $databases = array();
        }

        if (!empty($databases)) {
            foreach ($databases as $database => $file) {
                if (!file_exists($file)) {
                    $this->writeln('File ' . $file . ' does not exist', ConsoleKit\Colors::YELLOW);
                }
                else if (empty($database)) {
                    $this->writeln('No database set', ConsoleKit\Colors::YELLOW);
                }
                else {
                    $dialog = new ConsoleKit\Widgets\Dialog($this->getConsole());

                    if ($dialog->confirm('All data in ' . $database . ' will be replaced with ' . $file . '. Import ?')) {
                        // Backup if needed.
                        if ($this->getConfig('$.mysql.backup-on-import')) {
                            $this->getConsole()->execute('mysql', array('export', $database, $file . '.backup'), $options);
                        }

                        exec($this->getConfig('$.mysql.commands.mysql') . ' --user=root --password="' . $this->getConfig('$.mysql.users.root.password') . '" ' . $this->buildParameters($options, 'socket') . ' ' . $database . ' < ' . $file);
                        $this->writeln('File ' . $file . ' imported to ' . $database, ConsoleKit\Colors::GREEN);
                    }
                }
            }
        }
        else {
            $this->writeln('Missing arguments', ConsoleKit\Colors::YELLOW);
        }
    }

    public function executeUser(array $args = array(), array $options = array())
    {
        exec($this->getConfig('$.mysql.commands.mysql') . ' --user=root --password="' . $this->getConfig('$.mysql.users.root.password') . '" ' . $this->buildParameters($options, 'socket') . ' --execute="DELETE FROM mysql.user WHERE User!=\'\' AND User!=\'root\';"');

        $users = $this->getConfig('$.mysql.users');

        foreach ($users as $name => $user) {
            if ($name == 'root') {
                continue;
            }

            if (!$privileges = $this->getConfig('$.mysql.users.' . $name . '.privileges')) {
                $privileges = array('*.*' => array('all'));
            }

            foreach ($privileges as $on => $permissions) {
                exec($this->getConfig('$.mysql.commands.mysql') . ' --user=root --password="' . $this->getConfig('$.mysql.users.root.password') . '" ' . $this->buildParameters($options, 'socket') . ' --execute="GRANT ' . strtoupper(implode(', ', $permissions)) . ' ON ' . $on . ' TO \'' . $name . '\'@\'localhost\' IDENTIFIED BY \'' . $user['password'] . '\'"');
            }
        }
    }
}