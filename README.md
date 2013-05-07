PHP Database Abstraction Layer
==============================

This is a simple, clean database abastraction layer that can be used to
abstract database call to various different type of database servers.


Why another DBAL?
-----------------

There are excellent other projects out there that does DBAL well. To name a few:
* doctrine-project (http://www.doctrine-project.org/),
* Zend Framework 1 and 2 (http://framework.zend.com/)
* ADODb (http://phplens.com/adodb/)

However, I feel that for my needs, I want to do something a bit simpler. I
decided to build a database library that I can re-use. My goals for this
projects are:
* Simple and clean
* Reliable
* Easily maintainable


Requirements
------------

PHP 5.3 or above, extensions for the database type that you want to use enabled.


Usage
-----
Auto-loading needs to be set-up. A simple auto-loading mechanism example:

    set_include_path(get_include_path() . PATH_SEPARATOR . (dirname(__DIR__) . '/library'));

    spl_autoload_register(function($className) {
        if (class_exists($className, false)) return;
        if ('\\' === $className{0}) $className = substr($className, 1);
        include str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.class.php';
    });



License
-------

This project is released under Apache 2.0 license. You can find a copy of this
license in [LICENSE.txt](LICENSE.txt).
