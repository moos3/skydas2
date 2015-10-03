
/* Global Variables */
// Web socket server
var socket = io.connect('http://websocket.example.com:8080'),
	template_container,
	form_choices,
	// list your queue names from mod_fifo here
	list = [
  		'queue1',
  		'queue2',
  		'queue3',
  	];

var handleBarsHelper = {

	list: function() {
		// When converting caller to use handle js
		// Handlebars.registerHelper('list', function(items, options){
		//		var out = "<ul>";

		//		for (var i=0, l=items.length; i<l; i++) {
		// 			out = out + "<li>" + options.fn(items[i]) + "</li>";
		// 		}

		// 		return out + "</ul>";
		// });
	}
}

var buildProductList = function() {
	// Build out toggle list
	$.each(list, function(key, index){

		form_choices 		= (form_choices ? form_choices : "");
		template_container 	= (template_container ? template_container : "");

		form_choices += '<div class="tagbox">' +
						'<input class="checkbox" type="checkbox" name="module[]" value="" id="' + index + '" onclick="displayed(event);" checked>' +
						'<label for="' + index + '">' + index + '</label>' +
						'</div>';
		template_container += '<div id="' + index + '-data"><div class="table"></div></div>';

		$('#product_selection').html(form_choices);
		$('.loop').html(template_container);
	});
}

var displayed = function(event) {
    var target = event.target || event.srcElement,
        input_id = (target.id),
        data_id = (input_id + '-data'),
		input_obj = document.getElementById(input_id),
		data_obj = document.getElementById(data_id);

    if (input_obj.checked){
        data_obj.style.display = 'block';
    } else {
        data_obj.style.display = 'none';
    }
}

var selectToggle = function(event, toggle, form) {
     var myForm = document.forms[form],
         target = event.target || event.srcElement,
         target_ids = target.id,
         target_obj = document.getElementById(target_ids),
         classList = target.className.split(' ')[0],
         className = 'active';

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


window.onload = function() {

	buildProductList();

	socket.on('update', function(data) {
	    var queues = data.queues,
	    	agent = '',
	    	customer = '';
	    if ( typeof queues === 'object') {
	      	for ( var k in queues) {

		        var queue = queues[k],
		        	callers = queue.callers,
		        	agents = queue.agents;

		        queues['name'] = k;

		        /* Foundation for agent caller table */
		        var construct_agents_callers_table = {

				    agent_headers: '<thead>' +
									'<tr>' +
									'<th>Name</th>' +
									'<th>No Answer Count</th>' +
									'<th>Calls Answered</th>' +
									'<th>Extension</th>' +
									'</tr>' +
									'</thead>',

					caller_headers: '<thead>' +
									'<tr>' +
									'<th>CID Number</th>' +
									'<th>CID Name</th>' +
									'<th>State</th>' +
									'<th>Answered Time</th>' +
									'<th>Serving Agent</th>' +
									'<th>Agent Exten</th>' +
									'<th>Hold Time (Min:Sec)</th>' +
									'</tr>' +
									'</thead>',

					agents_content: function(agents) {
						agents.forEach(function(value) {
							agent += '<tr id="' + value.id + '"><td>' + value.name + '</td>' +
										'<td>' + value.no_answer_count + '</td>' + '<td>' + value.calls_answered + '</td>' + '<td>' + value.id + '</td>' + '</tr>';
						});
						return agent;
					},

				   	caller_content: function(callers) {
						callers.forEach(function(value) {
				        	customer += '<tr id="' + value.id + '"><td>' + value.caller_id + '</td><td>' + value.caller_name + '</td><td>' + value.status + '</td>' + '<td>' + value.answered_time + '</td>' + '<td>' + value.agent_name + '</td>' + '<td>' + value.agent_exten + '<td>' + value.hold_time + '</td>' + '</tr>';
				        });
				        return customer;
					}
				};

				/* Build out inital table tempalate */
				var source = '<div id="{{name}}-data" class="container-data">' +
								'<h1>{{name}}</h1>' +
								'<table class="agent-table">' + construct_agents_callers_table.agent_headers +
								'<tbody id="{{name}}-agents"></tbody>' +
								'</table>' +
								'<table class="caller-table">' + construct_agents_callers_table.caller_headers +
								'<tbody id="{{name}}-callers"></tbody>' +
								'</table>' +
								'</div>';

				var template = Handlebars.compile(source);
				var result = template(queues);
				$('#' + queues.name + '-data > .table').html(result);


				/* Inject Status */
		        if (typeof agents === 'object') {
		       		var selector_agents = $('#' + k + '-agents');

		        	if (k != null && k != '') {
		        		selector_agents.html(construct_agents_callers_table.agents_content(agents));
		        	} else {
			        	k = 'empty';
			        	selector_agents.html(construct_agents_callers_table.agents_content(agents));
			        }
		        }

				if (typeof callers === 'object') {
		        	if (callers != null && k != '') {
		          		var selector_callers = $('#' + k +'-callers');

		            	if (customer != null && !customer.length){
		              		selector_callers.html(construct_agents_callers_table.caller_content(callers));
		           		}
		          	}
		        }


			}
		}
	});
}
