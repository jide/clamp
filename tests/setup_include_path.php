<?php

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '../includes/ConsoleKit/src'),
    realpath(__DIR__ . '../includes'),
    get_include_path()
)));

// $path = realpath(dirname(__FILE__) . "/../includes/Clamp");
// echo($path);

// set_include_path(get_include_path() . PATH_SEPARATOR . $path);
// set_include_path(get_include_path() . PATH_SEPARATOR . 
//                         dirname(__FILE__) . "/../includes/Clamp");

?>