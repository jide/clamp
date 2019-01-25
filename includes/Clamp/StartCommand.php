<?php

namespace Clamp;

use ConsoleKit;

class StartCommand extends \Clamp\Command
{
    public function execute(array $args = array(), array $options = array())
    {
        # TODO: if we're on <10.14, check if port is 80 and warn that it probably won't work
        #       and that they should change to a non-priviledged port.
        $this->getConsole()->execute('apache', array('start'), array_merge($this->getConfig('$.apache.options'), $options));
        $this->getConsole()->execute('host', array('set'), array_merge($this->getConfig('$.host.options'), $options));
        $this->getConsole()->execute('mysql', array('start'), array_merge($this->getConfig('$.mysql.options'), $options));
    }
}