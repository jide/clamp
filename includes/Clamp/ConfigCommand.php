<?php

namespace Clamp;

use ConsoleKit;

class ConfigCommand extends \Clamp\Command
{
    public function executeWrite(array $args = array(), array $options = array())
    {
        copy($this->getConsole()->getOptionsParser()->getDefaultsFile(), $this->getConsole()->getOptionsParser()->getConfigFile());
    }
}