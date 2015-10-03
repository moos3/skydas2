// Create context using rabbit.js (cfr ZMQ)
// io and the subscriber socket.
var context = require('rabbit.js').createContext(),
    io = require('socket.io').listen(8081),
    sub = context.socket('SUB');

// set correct encoding
sub.setEncoding('utf8');

console.log('Starting the Socket.io server');
// a websocket is connected (eg: browser)
io.sockets.setMaxListeners(0);
io.sockets.on('connection', function(socket){
  // connect socket to updates exchange
  sub.connect('updates');
  // register handler that handles incoming data when the socket
  // detects new data on our queues
  // when receiving data, it gets pushed to the connected websocket
 // console.log('Connect to the AMPQ Server and Serving Data');
  sub.on('data', function(data){
    var message = JSON.parse(data);
    socket.emit(message.type, message.data);
  });
});
