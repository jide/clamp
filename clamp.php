<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/includes/ConsoleKit/src',
    __DIR__ . '/includes',
    get_include_path()
)));

spl_autoload_register(function($className) {
    if (substr($className, 0, 10) === 'ConsoleKit' || substr($className, 0, 5) === 'Clamp') {
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
        require_once $filename;
    }
});

//which php 2>/dev/null

$console = new Clamp\Console();
$console->addCommand('Clamp\HelpCommand');
$console->addCommand('Clamp\ApacheCommand');
$console->addCommand('Clamp\MysqlCommand');
$console->addCommand('Clamp\HostCommand');
$console->addCommand('Clamp\ControllerCommand');
$console->run();