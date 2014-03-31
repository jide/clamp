<?php

namespace Clamp;

use ConsoleKit;

class HostCommand extends \Clamp\Command
{
    public function executeStart(array $args = array(), array $options = array())
    {
        exec('echo ' . $this->getParameters() . ' | sudo tee -a /etc/hosts > /dev/null');
        exec('dscacheutil -flushcache');
        $this->writeln('Host set', ConsoleKit\Colors::GREEN);
    }

    public function executeStop(array $args = array(), array $options = array())
    {
        //exec('sudo sed -i 0 "' . $this->getParameters() . '"d /etc/hosts');
        exec('dscacheutil -flushcache');
        $this->writeln('Host unset', ConsoleKit\Colors::RED);
    }

    public function getDefaults()
    {
        return array(
            "127.0.0.1" => "localhost"
        );
    }

    protected function getParametrised($name, $option)
    {
        return "$name\t$option #clamp";
    }

    protected function getSeparator()
    {
        return  "\n";
    }
}