## SkyDas
A opensource operator Dashboard for freeswitch's mod_fifo. This is a realtime panel with websockets.

### Requirements
- rabbitmq
- nginx
- node.js
- php


## Configuration

#### Web View
We need to edit js/client.js. We need to update the following with the names of your fifo queues.
```
// list your queue names from mod_fifo here
list = [
    'queue1',
    'queue2',
    'queue3',
  ];
```
#### updater
We need to update the php configuration file for the updater script. `pusher/config.php` We need to update the following defines for your environment.

```
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
```
MQ_* is your RabbitMQ server information.

FS_* is your freesiwtch server information.

$myqueues is a list of your fifo queues.



## How to Run
To run the server part of panel. You will need a nginx server to proxy your request to the websocket server. To run the web socket server just execute `node pusher/server.js` Next to get updates pushed into the rabbitmq you will need to execute `php update.php`. This will poll the freeswitch server ever 5 seconds and then push the data to rabbitmq and then the web socket server will push to the clients.

## Coming Soon
Pusher and updater as a Go binary instead of requiring php running in the background to do this.
