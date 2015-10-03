<?php
require_once __DIR__ . '/lib/php-amqplib/vendor/autoload.php';
require_once __DIR__ . '/lib/xmlrpc/lib/xmlrpc.inc';
global $myqueues;

define('MQ_HOST', 'localhost');
define('MQ_PORT', 5672);
define('MQ_USER', 'guest');
define('MQ_PASS', 'guest');
define('MQ_VHOST', '/');
define('FS_HOST','freeswitch-server.com');
define('FS_USER','freeswitch');
define('FS_PASS','freswitchXMLRPCPassword');
define('FS_DOMAIN','freeswitch-server.com');
// List of your Queue Names
$myqueues = array('queue1', 'queue2', 'queue3');

//If this is enabled you can see AMQP output on the CLI
define('AMQP_DEBUG', true);
