<?php

namespace Clamp;

use ConsoleKit;

class HostCommand extends \Clamp\Command
{
    protected $parameter = '%1$s    %2$s #clamp';

    protected $separator = "\n";

    public function executeSet(array $args = array(), array $options = array())
    {
        $host = reset($this->getConfig("$.host.options"));
        if($host != 'localhost') {
            if (shell_exec('grep -R "' . $this->buildParameters($options) . '" /etc/hosts')) {
                exec('echo "' . $this->buildParameters($options) . '" | sudo tee -a /etc/hosts');
                exec('dscacheutil -flushcache');
                $this->writeln('Host set', ConsoleKit\Colors::GREEN);
            }
            else {
                $this->writeln('Host is already set', ConsoleKit\Colors::YELLOW);
            }
        }
    }

    public function executeUnset(array $args = array(), array $options = array())
    {
        $host = reset($this->getConfig("$.host.options"));
        if($host != 'localhost') {
            if (shell_exec('grep -R "' . $this->buildParameters($options) . '" /etc/hosts')) {
                exec('sudo sed -i "" -e "/' . preg_quote($this->buildParameters($options)) . '/d" /etc/hosts');
                exec('dscacheutil -flushcache');
                $this->writeln('Host unset', ConsoleKit\Colors::RED);
            }
            else {
                $this->writeln('Host is not set', ConsoleKit\Colors::YELLOW);
            }
        }
    }
}