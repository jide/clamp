<?php

namespace Clamp;

use ConsoleKit;

class StopCommand extends \Clamp\Command
{
    public function execute(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('stop'), $this->getConfig('$.apache.options'));
        $this->getConsole()->execute('host', array('unset'), $this->getConfig('$.host.options'));
        $this->getConsole()->execute('mysql', array('stop'), $this->getConfig('$.mysql.options'));
    }
}