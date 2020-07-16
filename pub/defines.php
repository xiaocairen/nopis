<?php

/*
|--------------------------------------------------------------------------
| 这里可以定义一些用户自定义常量
|--------------------------------------------------------------------------
|
*/

define('HTTP_HOST',     implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2)));
define('IS_POST',       strtoupper($_SERVER['REQUEST_METHOD']) == 'POST');

