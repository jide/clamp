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

require_once 'JsonPath/JsonPath.php';

$commands = array(
	'Clamp\HelpCommand',
	'Clamp\ApacheCommand',
	'Clamp\HostCommand',
	'Clamp\MysqlCommand',
	'Clamp\ControllerCommand'
);

$console = new ConsoleKit\Console($commands, new Clamp\ConfigOptionsParser());
$console->run();