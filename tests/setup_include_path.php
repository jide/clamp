<?php

$paths = array(
    realpath(__DIR__ . DIRECTORY_SEPARATOR .  '../includes'),
    // realpath(__DIR__ . DIRECTORY_SEPARATOR .  '../includes/ConsoleKit/src'),
    // realpath(__DIR__ . DIRECTORY_SEPARATOR . '../includes/Clamp')
);

set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));

require_once 'JsonPath/JsonPath.php';

print(get_include_path());

?>