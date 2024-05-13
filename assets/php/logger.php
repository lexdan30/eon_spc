<?php

/// Abstract Class which maps an appropiate symbol which added as prefix for each log message
///
/// - error: Log type error
/// - info: Log type info
/// - debug: Log type debug
/// - verbose: Log type verbose
/// - warning: Log type warning
/// - severe: Log type severe
abstract class LogEvent {
    const error      = "[❗❗]"; // error
    const info       = "[ℹ]"; // info
    const debug      = "[💬]"; // debug
    const verbose    = "[🔬]"; // verbose
    const warning    = "[⚠️]"; // warning
    const severe     = "[🔥]"; // severe
}

class Log {

    // MARK: - Loging methods
        
    /// obj: Object or message to be logged
    /// line: Line number in file from where the logging is done
    /// func_name: Name of the function from where the logging is done
    /// file_name: File name from where loggin to be done

    // Logs error messages on php_error with prefix [❗❗]
    static function e($obj, $func_name = 'e') {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[0];
        $line = $debug_backtrace['line'];
        $file_name = $debug_backtrace['file'];
        date_default_timezone_set("Asia/Manila"); 
        error_log(date("d-m-Y H:i:s", time())." ".LogEvent::error." ".basename($file_name, '.php').": Line ".$line." ".$func_name." -> ".$obj);
    }

    // Logs info messages on php_error with prefix [ℹ]
    static function i($obj, $func_name = 'i') {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[0];
        $line = $debug_backtrace['line'];
        $file_name = $debug_backtrace['file'];
        date_default_timezone_set("Asia/Manila"); 
        error_log(date("d-m-Y H:i:s", time())." ".LogEvent::info." ".basename($file_name, '.php').": Line ".$line." ".$func_name." -> ".$obj);
    }

    // Logs debug messages on php_error with prefix [💬]
    static function d($obj, $func_name = 'd') {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[0];
        $line = $debug_backtrace['line'];
        $file_name = $debug_backtrace['file'];
        date_default_timezone_set("Asia/Manila"); 
        error_log(date("d-m-Y H:i:s", time())." ".LogEvent::debug." ".basename($file_name, '.php').": Line ".$line." ".$func_name." -> ".$obj);
    }
    
    // Logs messages verbosely on php_error with prefix [🔬]
    static function v($obj, $func_name = 'v') {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[0];
        $line = $debug_backtrace['line'];
        $file_name = $debug_backtrace['file'];
        date_default_timezone_set("Asia/Manila"); 
        error_log(date("d-m-Y H:i:s", time())." ".LogEvent::verbose." ".basename($file_name, '.php').": Line ".$line." ".$func_name." -> ".$obj);
    }

    // Logs warnings verbosely on php_error with prefix [⚠️]
    static function w($obj, $func_name = 'w') {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[0];
        $line = $debug_backtrace['line'];
        $file_name = $debug_backtrace['file'];
        date_default_timezone_set("Asia/Manila"); 
        error_log(date("d-m-Y H:i:s", time())." ".LogEvent::warning." ".basename($file_name, '.php').": Line ".$line." ".$func_name." -> ".$obj);
    }

    // Logs severe events on php_error with prefix [🔥]
    static function s($obj, $func_name = 's') {
        $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[0];
        $line = $debug_backtrace['line'];
        $file_name = $debug_backtrace['file'];
        date_default_timezone_set("Asia/Manila"); 
        error_log(date("d-m-Y H:i:s", time())." ".LogEvent::severe." ".basename($file_name, '.php').": Line ".$line." ".$func_name." -> ".$obj);
    }
}
?>