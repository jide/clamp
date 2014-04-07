<?php

namespace Clamp;

use ConsoleKit;

class StartCommand extends \Clamp\Command
{
    public function execute(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('start'), $this->getConfig('$.apache.options'));
        $this->getConsole()->execute('host', array('set'), $this->getConfig('$.host.options'));
        $this->getConsole()->execute('mysql', array('start'), $this->getConfig('$.mysql.options'));
    }
}