<?php

namespace Clamp;

use ConsoleKit;

class ControllerCommand extends \Clamp\Command
{
    public function executeStart(array $args = array(), array $options = array())
    {
        $this->getConsole()->run(array('apache', 'start'));
        $this->getConsole()->run(array('host', 'start'));
        $this->getConsole()->run(array('mysql', 'start'));
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        $this->getConsole()->run(array('apache', 'stop'));
        $this->getConsole()->run(array('host', 'stop'));
        $this->getConsole()->run(array('mysql', 'stop'));
    }
}