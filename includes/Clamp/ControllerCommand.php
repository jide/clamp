<?php

namespace Clamp;

use ConsoleKit;

class ControllerCommand extends \Clamp\Command
{
    public function executeStart(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('start'), $this->getOptions());
        $this->getConsole()->execute('host', array('start'), $this->getOptions());
        $this->getConsole()->execute('mysql', array('install'), $this->getOptions('basedir', 'datadir'));
        $this->getConsole()->execute('mysql', array('create-db'), $this->getOptions('socket', 'db'));
        $this->getConsole()->execute('mysql', array('start'), $this->getOptions());
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('stop'), $this->getOptions());
        $this->getConsole()->execute('host', array('stop'), $this->getOptions());
        $this->getConsole()->execute('mysql', array('stop'), $this->getOptions('basedir', 'datadir'));
    }
}