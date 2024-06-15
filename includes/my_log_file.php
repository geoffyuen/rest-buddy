<?php
function my_log_file($msg, $name = '')
{
    // Print the name of the calling function if $name is left empty
    $trace = debug_backtrace();
    $name = ('' == $name) ? $trace[1]['function'] : $name;

    $error_dir = 'rest-buddy_error.log';
    $msg = print_r($msg, true);
    $log = $name . "  |  " . $msg . "\n";
    error_log($log, 3, $error_dir);
}
