<?php
require_once __DIR__ . '/lib/php-amqplib/vendor/autoload.php';
require_once __DIR__ . '/lib/xmlrpc/lib/xmlrpc.inc';

define('HOST', 'localhost');
define('PORT', 5672);
define('USER', 'guest');
define('PASS', 'guest');
define('VHOST', '/');

//If this is enabled you can see AMQP output on the CLI
define('AMQP_DEBUG', true);
