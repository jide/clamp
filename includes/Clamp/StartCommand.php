<?php

namespace Clamp;

use ConsoleKit;

class StartCommand extends \Clamp\Command
{
    public function execute(array $args = array(), array $options = array())
    {
        # if we're on <10.14 and port is 80, warn that this probably won't work
        # and that they should change to a non-privileged port.
        if($this->sudoRequired()) {
            $warning = <<<EOT
WARNING: Mac OS X 10.13 and lower requires sudo to run on ports lower than 1024.
         Please select a port higher than 1023 (currently using port $port).
         This command will likely fail.

EOT;
            $this->writeln($warning, ConsoleKit\Colors::YELLOW);
        }

        $this->getConsole()->execute('apache', array('start'), array_merge($this->getConfig('$.apache.options'), $options));
        // $this->getConsole()->execute('host', array('set'), array_merge($this->getConfig('$.host.options'), $options));
        $this->getConsole()->execute('mysql', array('start'), array_merge($this->getConfig('$.mysql.options'), $options));
    }
}