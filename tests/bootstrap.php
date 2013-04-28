<?php
set_include_path(get_include_path() . PATH_SEPARATOR . (dirname(__DIR__) . '/library'));

spl_autoload_register(function($className) {
    if (class_exists($className, false)) return;
    if ('\\' === $className{0}) $className = substr($className, 1);
    @include str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.class.php';
});
