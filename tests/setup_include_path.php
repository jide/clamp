<?php

$paths = array(
    realpath(__DIR__ . DIRECTORY_SEPARATOR .  '../includes/JsonPath'),
);

set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $paths));

?>