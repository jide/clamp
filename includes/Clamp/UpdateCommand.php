<?php

namespace Clamp;

use ConsoleKit;

class UpdateCommand extends \Clamp\Command
{
    public function execute(array $args, array $options = array())
    {
        exec('curl http://jide.github.io/clamp/install.sh | sh');
    }
}