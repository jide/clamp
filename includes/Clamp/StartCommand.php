<?php

namespace Clamp;

use ConsoleKit;

class StartCommand extends \Clamp\Command
{
    public function execute(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('start'), array_merge($this->getConfig('$.apache.options'), $options));
        $this->getConsole()->execute('host', array('set'), array_merge($this->getConfig('$.host.options'), $options));
        $this->getConsole()->execute('mysql', array('start'), array_merge($this->getConfig('$.mysql.options'), $options));
    }
}