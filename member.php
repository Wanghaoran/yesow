<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '.yesow.com');
//ini_set('session.cookie_lifetime', '1800');
session_set_cookie_params(1800 , '/', '.yesow.com');
define('APP_DEBUG', true);
define('APP_NAME', 'member');
define('APP_PATH', './member/');
include './app/ThinkPHP.php';
