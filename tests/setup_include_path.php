<?php

$paths = array(
    realpath(__DIR__ . DIRECTORY_SEPARATOR .  '../includes'),
);

set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));

require_once 'JsonPath/JsonPath.php';

?>