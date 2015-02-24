window.onload = function()
{
  var socket = io.connect('http://localhost:8080');
  socket.on('update', function(data){
    var queues = data.queues;
    var agent = '';
    var customer = '';

    if ( typeof queues === 'object'){
      for( var k in queues){
        var queue = queues[k];
        console.log(k);
        console.log(queues[k]);
        var callers = queue.callers;
        var agents = queue.agents;

        if ( typeof agents === 'object' ){
          agents.forEach(function(value){
            agent += '<tr id="' + value.id + '"><td>' + value.name + '</td><td>' + value.status + '</td>' + '<td>' + value.state + '</td>' +
            '<td>' + value.no_answer_count + '</td>' + '<td>' + value.calls_answered + '</td>' + '<td>' + value.queue_state + '</td>' +
            '<td>' + value.level + '</td>' + '<td>' + value.position + '</td>' + '</tr>';
          });
          console.log(agent);
          if (k != null && k != ''){
            document.querySelector('#'+ k + '-agents').innerHTML = agent;
          } else {
	    k = 'empty';
	    document.querySelector('#'+ k + '-agents').innerHTML = agent;
	  }
        }
        if ( typeof callers === 'object' ){
          callers.forEach(function(value){
            customer += '<tr id="' + value.id + '"><td>' + value.cid_number + '</td><td>' + value.cid_name + '</td><td>' + value.state + '</td>' + '<td>' + value.score +'</tr>';
          });
          if (customer != null){
            document.querySelector('#'+ k +'-callers').innerHTML = customer;
          }
        }

      }
    }
});

}
