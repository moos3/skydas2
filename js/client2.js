window.onload = function()
{
  var socket = io.connect('http://panel.symplicity.com:8080');
  socket.on('update', function(data){
    var queues = data.queues;
    var agent = '';
    var customer = '';

    if ( typeof queues === 'object'){
      for( var k in queues){
        var queue = queues[k];
        var callers = queue.callers;
        var agents = queue.agents;
        console.log(queues);
        if ( typeof agents === 'object' ){
          agents.forEach(function(value){
            agent += '<tr id="' + value.id + '"><td>' + value.name + '</td><td>' + value.status + '</td>' + '<td>' + value.state + '</td>' +
            '<td>' + value.no_answer_count + '</td>' + '<td>' + value.calls_answered + '</td>' + '<td>' + value.id + '</td>' +
            '<td>' + k + '</td>' + '</tr>';
          });

          if (k != null && k != ''){
            document.querySelector('#'+ k + '-agents').innerHTML = agent;
          } else {
	           k = 'empty';
	           document.querySelector('#'+ k + '-agents').innerHTML = agent;
	        }
        }
        if ( typeof callers === 'object' ){
          if (callers != null && k != ''){
            callers.forEach(function(value){
              console.log(value);
              customer += '<tr id="' + value.id + '"><td>' + value.caller_id + '</td><td>' + value.caller_name + '</td><td>' + value.status + '</td>' + '<td>' + value.answered_time + '</td>' + '<td>' + value.agent_name + '</td>' + '<td>' + value.agent_exten + '</tr>';
            });
            if (customer != null){
              document.querySelector('#'+ k +'-callers').innerHTML = customer;
            }
          }
        }
      }
    }
});

}

function displayed() {
    var target = event.target || event.srcElement,
        input_id = (target.id),
        data_id = (input_id + '-data'),
	input_obj = document.getElementById(input_id),
	data_obj = document.getElementById(data_id);

    //console.log(('#' + input_id), input_obj);
    //console.log(('#' + data_id), data_obj);

    if (input_obj.checked){
        data_obj.style.display = 'block';
    } else {
        data_obj.style.display = 'none';
    }
}

function selectToggle(toggle, form) {
     var myForm = document.forms[form],
         target = event.target || event.srcElement,
         target_ids = target.id,
         target_obj = document.getElementById(target_ids),
         classList = target.className.split(' ')[0],
         className = 'active';

//console.log(classList[0]);
//console.log(target_ids, target_obj);

if (classList === 'active') {
	//console.log('active');
} else {
	//console.log('inactive');
}


     for( var i=0; i < myForm.length; i++ ) {
          var ids = myForm.elements[i].id,
              data_ids = (ids + '-data'),
              data_obj = document.getElementById(data_ids);

          if (toggle) {
               myForm.elements[i].checked = 'checked';
               data_obj.style.display = 'block';
          } else {
               myForm.elements[i].checked = '';
               data_obj.style.display = 'none';
          }
     }
}
