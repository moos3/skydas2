<?php

require_once __DIR__ . '/config.php';

define('FS_HOST','localhost');
define('FS_USER','freeswitch');
define('FS_PASS','freeswitch');

function buildJson($fifo_data){
  /* Agent
  array(
    'id' => '203',
    'name' => 'Sue',
    'status' => 'Available',
    'state' => 'Receiving',
    'no_answer_count' => rand(0, 500),
    'calls_answered' => rand(0, 100),
    'queue' => 'main@default',
    'queue_state' => 'offering',
    'level' => '1',
    'position' => '2'
  )
  */
  /* Caller
  array(
   'id' => '1eb0a17-92a6-49f2-9eb5-8f0b53251659',
   'queue' => 'main@default',
   'session_uuid' => 'b88296ca-9aab-49dd-a2a6-55c339e67128',
   'cid_number' => '+14045550862',
   'cid_name' => 'ATLANTA,ME',
   'join_epoch' => '1409069598',
   'system_epoch' => '1409069590',
   'bridged_epoch' => '1409069736',
   'serving_agent' => '269@default',
   'state' => 'Answered',
   'score' => rand(0,4500),
   'holdtime' => rand(0,10).':'.rand(0,59)
 )*/
 $queue = array();
 $agents = $fifo_data->fifo->outbound;
 var_dump($agents);exit;
 foreach($fifo_data->fifo->outbound as $agents){
   get_object_vars($agents);
   exit;
 }
 $callers = $fifo_data->fifo->callers;
 $consumers = $fifo_data->fifo->consumers;
 $bridges = $fifo_data->fifo->bridges;
 return $queue;
}

function fetchFifoLive(){
  $client = new xmlrpc_client('/RPC2', FS_HOST, 8080);
  $client->setCredentials(FS_USER, FS_PASS, null);

  $msg = new xmlrpcmsg('freeswitch.api',
         array(
                 new xmlrpcval("fifo", "string"),
                 new xmlrpcval("list testqueue", "string"),
                 )
        );
  $res = $client->send($msg);
  $list = $res->val->me['string'];
  var_dump($list);
  $xml = simplexml_load_string($list);
  $push = buildJson($xml);
  return $push;
}

var_dump(fetchFifoLive());
exit;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Create a connection with RabbitMQ server.
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Create a fanout exchange.
// A fanout exchange broadcasts to all known queues.
$channel->exchange_declare('updates', 'fanout', false, false, false);
$myqueues = array('csm','advocate','horizon','voice', 'accounts',
                  'insight', 'accommodate','residence','reflection',
                  'onestop','community','ascend','residence');

// Create and publish the message to the exchange.
while (true)
{
    $data = array(
        'type' => 'update',
        'data' => array(
         'queues' => array(
            )
          )
    );
    $message = new AMQPMessage(json_encode($data));
    $channel->basic_publish($message, 'updates');
    sleep(3);
}

// Close connection.
$channel->close();
$connection->close();
