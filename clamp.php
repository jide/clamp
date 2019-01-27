<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/includes/ConsoleKit/src',
    __DIR__ . '/includes',
    get_include_path()
)));

spl_autoload_register(function($className) {
    $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
    require_once $filename;
});

require_once 'JsonPath/JsonPath.php';

$commands = array(
    'Clamp\HelpCommand',
    'Clamp\ApacheCommand',
    'Clamp\HostCommand',
    'Clamp\MysqlCommand',
    'Clamp\StartCommand',
    'Clamp\StopCommand',
    'Clamp\ConfigCommand',
);

$console = new ConsoleKit\Console($commands, new Clamp\ConfigOptionsParser());
$console->run();