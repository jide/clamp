<?php

namespace Clamp;

use ConsoleKit;

class HostCommand extends \Clamp\Command
{
    protected $parameter = '%1$s	%2$s #clamp';

    protected $separator = "\n";

    public function executeSet(array $args = array(), array $options = array())
    {
        exec('echo "' . $this->buildParameters($options) . '" | sudo tee -a /etc/hosts');
        exec('dscacheutil -flushcache');
        $this->writeln('Host set', ConsoleKit\Colors::GREEN);
    }

    public function executeUnset(array $args = array(), array $options = array())
    {
        exec('sudo sed -i "" -e "/' . preg_quote($this->buildParameters($options)) . '/d" /etc/hosts');
        exec('dscacheutil -flushcache');
        $this->writeln('Host unset', ConsoleKit\Colors::RED);
    }
}