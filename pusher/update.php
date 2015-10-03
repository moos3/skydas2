<?php

require_once (__DIR__ . '/config.php');

function buildJson($fifo_data) {
    $data = array(
        'type' => 'update',
        'data' => array(
            'queues' => array()));

    if (count($fifo_data->fifo)) {
        foreach ($fifo_data->fifo as $_queue) {
            $_qdata = get_object_vars($_queue);
            $_qdata = $_qdata['@attributes'];

            $queue = array();

            // These are Agents
            if (count($fifo_data->fifo->outbound->member)) {
                $_support = array();
                foreach($fifo_data->fifo->outbound->member as $agents){
                    $_data = get_object_vars($agents->attributes());
                    $_data = $_data['@attributes'];
                    $extension = explode('/',$agents->__toString());
                    $extension = explode('@',$extension[1]);
                    $_support[] = array(
						            'id' => $extension[0],
                        'name' => fetchExtensionData($extension[0],'directory_full_name'),
                        'status' => $_data['status'],
                        'no_answer_count' => $_data['outbound-fail-total-count'],
                        'calls_answered' => $_data['outbound-call-total-count'],
                        'queue_join_time' => $_data['logged-on-since'],
                    );
                }
                $queue['agents'] = $_support;
            }

            $_callers = array();
            ### Callers waiting for a agent to answer
            if (count($fifo_data->fifo->callers->caller)) {
                foreach($fifo_data->fifo->callers->caller as $calls){
                    $_data = get_object_vars($calls->attributes());
                    $_data = $_data['@attributes'];
                    $_callers[$_data['uuid']] = array(
                        'id' => $_data['uuid'],
                        'caller_id' => urldecode($_data['caller_id_number']),
                        'caller_name' => urldecode($_data['caller_id_name']),
                        'status' => $_data['status'],
                        'agent_name' => '',
                        'agent_exten' => '',
                        'queue_join_time' => $_data['timestamp'],
                        'answered_time' => '',
                        'hold_time' => date('i:s', (strtotime('now') - strtotime($_data['timestamp']))),
                    );
                }
            }
            ## Callers go here once the call is answered
            if (count($fifo_data->fifo->bridges->bridge)) {
                foreach($fifo_data->fifo->bridges->bridge as $bridge){
                    $_data = get_object_vars($bridge->attributes());
                    $_data = $_data['@attributes'];
                    foreach ($bridge->caller as $caller) {
                        $_cdata = get_object_vars($caller->attributes());
                        $_cdata = $_cdata['@attributes'];
                    }
                    $call_info = fetchCallInfo($_cdata['uuid']);
            		    $calldata = array(
            				      'id' => $_cdata['uuid'],
            				      'caller_id' => urldecode($call_info['Caller-Orig-Caller-ID-Number']),
            				      'caller_name' => urldecode($call_info['Caller-Orig-Caller-ID-Name']),
            				      'agent_exten' => $call_info['Other-Leg-Caller-ID-Number'],
            				      'agent_name' => fetchExtensionData($call_info['Other-Leg-Caller-ID-Number'], 'directory_full_name'),
            				      'answered_time' => $_data['bridge_start'],
                                  'hold_time' => date('i:s', (strtotime($_data['bridge_start']) - strtotime(date('Y-M-d H:i:s', floor($call_info['Caller-Channel-Answered-Time']/1000000))))),
            				      'status' => 'ANSWERED');
            		    if (is_array($_callers[$_cdata['uuid']])) {
            		      $_callers[$_cdata['uuid']] = array_merge(
            							       $_callers[$_cdata['uuid']],
            							       $calldata);
            		    } else {
            		      $_callers[$_cdata['uuid']] =$calldata;
            		    }
                    $_support['agents'][$call_info['Other-Leg-Caller-ID-Number']] = array_merge(
                            $_support['agents'][$call_info['Other-Leg-Caller-ID-Number']],
                            array(
                                'status' => 'Talking',
                            )
                        );
                }
            }
      	    $_newcallers = array();
      	    if (count($_callers)) {
      	      foreach ($_callers as $_caller) {
      		        $_newcallers[] = $_caller;
      	      }
      	    }
            $queue['callers'] = $_newcallers;
            $data['data']['queues'][$_qdata['name']] = $queue;
        }
    }
    return $data;
}

function fetchFifoLive($queue){
    $client = new xmlrpc_client('/RPC2', FS_HOST, 8080);
    $client->setCredentials(FS_USER, FS_PASS, null);
    $msg = new xmlrpcmsg('freeswitch.api',
                         array(
                             new xmlrpcval("fifo", "string"),
                             new xmlrpcval("list " . $queue, "string"),
      )
    );
    $res = $client->send($msg);
    $list = $res->val->me['string'];
    $xml = simplexml_load_string($list);
    $push = buildJson($xml);
    return $push;
}

function fetchExtensionData($extension, $arg){
  $client = new xmlrpc_client('/RPC2', FS_HOST, 8080);
  $client->setCredentials(FS_USER, FS_PASS, null);

  $msg = new xmlrpcmsg('freeswitch.api',
         array(
                 new xmlrpcval("user_data", "string"),
                 new xmlrpcval($extension . FS_DOMAIN . " " . $arg, "string"),
                 )
        );
  $res = $client->send($msg);
  $point = $res->val->me['string'];
  return $point;
}

function fetchCallInfo($uuid){
  $client = new xmlrpc_client('/RPC2', FS_HOST, 8080);
  $client->setCredentials(FS_USER, FS_PASS, null);

  $msg = new xmlrpcmsg('freeswitch.api',
         array(
                 new xmlrpcval("uuid_dump", "string"),
                 new xmlrpcval($uuid, "string"),
                 )
        );
  $res = $client->send($msg);
  $point = $res->val->me['string'];
  $keys = explode("\n",$point);
  $point = array();
  foreach($keys as $key){
    $data = explode(':',$key);
    $point[$data[0]] = $data[1];
  }
  return $point;

}

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Create a connection with RabbitMQ server.
$connection = new AMQPConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASS);
$channel = $connection->channel();

// Create a fanout exchange.
// A fanout exchange broadcasts to all known queues.
$channel->exchange_declare('updates', 'fanout', false, false, false);

// Create and publish the message to the exchange.
while (true)
{
    foreach($myqueues as $queue){
        $data = fetchFifoLive($queue);
        $message = new AMQPMessage(json_encode($data));
        $channel->basic_publish($message, 'updates');
        var_dump(json_encode($data));
    }
    sleep(3);
}

// Close connection.
$channel->close();
$connection->close();
