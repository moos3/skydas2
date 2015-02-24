<?php

require_once __DIR__ . '/config.php';
//require_once __DIR__ . '/lib/test_engine.php';

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
          $myqueues[rand(0,count($myqueues))] => array(
            'agents' => array(
              array(
                'id' => '2009',
                'name' => 'Carly',
                'status' => 'Available',
                'state' => 'Receiving',
                'no_answer_count' => rand(0, 500),
                'calls_answered' => rand(0, 100),
                'queue' => 'csm@default',
                'queue_state' => 'offering',
                'level' => '1',
                'position' => '1'
              ),
              array(
                'id' => '0203',
                'name' => 'Sue',
                'status' => 'Available',
                'state' => 'Receiving',
                'no_answer_count' => rand(0, 500),
                'calls_answered' => rand(0, 100),
                'queue' => 'csm@default',
                'queue_state' => 'offering',
                'level' => '1',
                'position' => '2'
              )
            ),
            'callers' => array(
                 array(
                  'id' => '1eb0a17-92a6-49f2-9eb5-8f0b53251659',
                  'queue' => 'csm@default',
                  'session_uuid' => 'b88296ca-9aab-49dd-a2a6-55c339e67128',
                  'cid_number' => '+14046170862',
                  'cid_name' => 'ATLANTA,GA',
                  'join_epoch' => '1409069598',
                  'system_epoch' => '1409069590',
                  'bridged_epoch' => '1409069736',
                  'serving_agent' => '8269@default',
                  'state' => 'Answered',
                  'score' => rand(0,4500),
                  'holdtime' => rand(0,10).':'.rand(0,59)

                )
            )
          )
          )
          )
    );
    if ($check = rand(0,2) == '1' ) {
      unset ($data['data']['agents']);
    }
    if ($check = rand(0,4) == '3') {
      unset ($data['data']['callers']);
    }
    $message = new AMQPMessage(json_encode($data));
    $channel->basic_publish($message, 'updates');
    sleep(3);
}

// Close connection.
$channel->close();
$connection->close();
