<?php

function generateAgent(){
  $name = load_agent_names();
  $id = rand(1000,9999);
  $status = 'Avaiable';
  $state = 'Receiving';
  $queue = 'csm@default';
  $queue_state = 'offering';
  $level = rand(1,2);
  $postion = rand(0,15);

  $struct = array(
    'id' => $id,
    'name' => $name,
    'status' => $status,
    'state' => $state,
    'no_answer_count' => rand(0, 500),
    'calls_answered' => rand(0, 100),
    'queue' => $queue,
    'queue_state' => $queue_state,
    'level' => $level,
    'position' => $postion
  );
  return $struct;
}

function generateCaller(){
  $id = uniqid();
  $queue = 'csm@default';
  $session_uuid = uniqid();
  $cid_number = '+1703'.rand(200,790).rand(1000,9999);
  $cid_number = 'Unknown Caller';
  $serving_agent = '';
  $state = 'Waiting';
  $struct = array(
   'id' => $id,
   'queue' => $queue,
   'session_uuid' => 'b88296ca-9aab-49dd-a2a6-55c339e67128',
   'cid_number' => '+14046170862',
   'cid_name' => 'ATLANTA,GA',
   'join_epoch' => '1409069598',
   'system_epoch' => '1409069590',
   'bridged_epoch' => '1409069736',
   'serving_agent' => '8269@default',
   'state' => 'Answered',
   'score' => rand(0,4500)
 );
 return $struct;
}
