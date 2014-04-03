<?php

namespace Clamp;

use ConsoleKit;

class ControllerCommand extends \Clamp\Command
{
    public function executeStart(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('start'), $this->getConfig('$.apache.options'));
        $this->getConsole()->execute('host', array('start'), $this->getConfig('$.host.options'));
        $this->getConsole()->execute('mysql', array('start'), $this->getConfig('$.mysql.options'));
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('stop'), $this->getConfig('$.apache.options'));
        $this->getConsole()->execute('host', array('stop'), $this->getConfig('$.host.options'));
        $this->getConsole()->execute('mysql', array('stop'), $this->getConfig('$.mysql.options'));
    }
}