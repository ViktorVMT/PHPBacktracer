# PHPBacktracer
# Small,powerfull and customizable library to handle with E errors,Exceptions, biuld-in functionality to write log files, log custom data and retrieve filtered data from log files . 

## Class Features

- handle with E errors
- handle with Exceptions
- write logs in different files, not only for erros, according to your need
- show/hide errors in run time
- retrieve filtered data from log files by case ( log , error , exception ), keyword, limit in rows, descading order

## Why you might need it

Many PHP developers utilize logs and backtrace in their code. PHPBacktracer is easy to deploy and provide information for debugging

## License

This software is licenced under the MIT. Please read LICENSE for information on the software availability and distribution.

 ## Installation

 PHPBacktracer is available via [Composer/Packagist](https://packagist.org/packages/pertinax/PHPBacktracer).
 Alternatively, just copy the contents of the PHPBacktracer folder into somewhere that's in your PHP `include_path` setting. If you don't speak git or just want a tarball, click the 'Download ZIP' button at the top of the page in GitHub.

## A Simple Examples

```php
<?php
require 'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
// first param is the directory, the second one is key-value. In this case we use logs/errors.log
// for E errors
Track::settings('logs',array('errors' => 'errors.log'));
?>
```
To put in separate folder errors, exceptions and logs:
``` 
Track::settings('logs',array('errors' => 'errors.log','exceptions' => 'exceptions.log','trace' => 'log.log'));
```
To disable display option ( By default is enabled ):
```php 
Track::settings('logs',array('errors' => 'errors.log','exceptions' => 'exceptions.log','trace' => 'log.log'),array('displayMessage' => false));
```
To write single message into log file: 
```php
Track::log('just random text');
```
To disable display message and add a custom path 
Notice: if you specify another route, make sure it's created already
```php
Track::log('just random text',array('displayMessage' => true,'route' => 'anotherDir/anotherFile.log'));
```
To retrieve data from log file:
```php
Track::retrieveLogs(array('exceptions.log'));
```
To retrieve data from multiple files
```php
Track::retrieveLogs(array('exceptions.log','errors.log','log.log')));
```
To set filter for the previous day, search by case ( error, exception or log ) and by keyword
```php
Track::retrieveLogs(array('exceptions.log','errors.log'),array('date' => '1 day','case' => 'error', 'keyword' => 'in'));
```
To set filter for the previous day, search by case ( error, exception or log ) and by keyword in descading order, limit by 10
```php
Track::retrieveLogs(array('exceptions.log','errors.log'),array('date' => '1 day','case' => 'error', 'keyword' => 'in'),true,10);
```
