<?php

namespace Clamp;

use ConsoleKit;

class HelpCommand extends ConsoleKit\Command
{
    public function execute(array $args, array $options = array())
    {
        if (empty($args)) {
            $formater = new ConsoleKit\TextFormater(array('quote' => ' * '));
            $this->writeln('Available commands:', ConsoleKit\Colors::BLACK | ConsoleKit\Colors::BOLD);
            foreach ($this->console->getCommands() as $name => $fqdn) {
                if ($fqdn !== __CLASS__) {
                    $this->writeln($formater->format($name));
                }
            }
            $this->writeln("Use 'clamp help command' for more info");
        } else {
            $commandFQDN = $this->console->getCommand($args[0]);
            $help = ConsoleKit\Help::fromFQDN($commandFQDN, ConsoleKit\Utils::get($args, 1));
            $this->writeln($help);
        }
    }
}