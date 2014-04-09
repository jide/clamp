<?php

namespace Clamp;

use ConsoleKit;

class StopCommand extends \Clamp\Command
{
    public function execute(array $args = array(), array $options = array())
    {
        $this->getConsole()->execute('apache', array('stop'), array_merge($this->getConfig('$.apache.options'), $options));
        $this->getConsole()->execute('host', array('unset'), array_merge($this->getConfig('$.host.options'), $options));
        $this->getConsole()->execute('mysql', array('stop'), array_merge($this->getConfig('$.mysql.options'), $options));
    }
}