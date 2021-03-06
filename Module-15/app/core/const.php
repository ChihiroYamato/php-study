<?php

/**
 * Константы модуля
 */

if (! defined('PROJECT_CORE')) {
    define('PROJECT_CORE', true);
}
if (! defined('PROJECT_SERVER_PATH')) {
    define('PROJECT_SERVER_PATH', '/php-developer-base/Module-15');
}
if (! defined('PROJECT_LOCAL_PATH')) {
    define('PROJECT_LOCAL_PATH', dirname(__DIR__, 2));
}
if (! defined('LOGS_PATH')) {
    define('LOGS_PATH', dirname(__DIR__) . '/logs/');
}
if (! defined('STORAGE_BASE_PATH')) {
    define('STORAGE_BASE_PATH', dirname(__DIR__) . '/');
}
if (! defined('STORAGE_DIR')) {
    define('STORAGE_DIR', 'storage');
}
if (! defined('EXCEPTION_LOGS_NAME')) {
    define('EXCEPTION_LOGS_NAME', 'exception-logs.xml');
}
if (! defined('STORAGE_LOGS_NAME')) {
    define('STORAGE_LOGS_NAME', 'file-storage-log');
}
