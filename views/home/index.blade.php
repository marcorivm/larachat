@layout('larachat::layouts.main')

@section('main-chat')
<div class="row-fluid">
	<div class="span12 well">
		<ul class="nav nav-tabs" id="chats">
			<li class="active"><a href="#tab-1" id="link-1">General</a></li>			
		</ul>
		<div class="tab-content" id="tabs">
			<div class="tab-pane active" id="tab-1">
				<table id="-1" class="table table-striped table-bordered">
					@forelse ($global_messages as $message)
						<tr data-messageid="{{ $message->id }}"><td>{{ $message->nick }}: {{ $message->message }}</td></tr>
					@empty
						No hay mensajes!
					@endforelse
				</table>		
			</div>			
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12 well">
		<form method="POST" action="/chat/message" data-to="-1">
			<div class="span10">
				<input type="text" id="texto" name="message" class="span11" />
			</div>
			<div class="span1">
				<input type="submit" class="btn btn-large" value="Enviar" />
				<!-- <button id="send" class="btn btn-large">Enviar</button> -->
			</div>
		</form>
	</div>
</div>
@endsection

@section('right-bar')
	<table id="users" class="table table-striped table-bordered table-hover">
		@forelse ($online_users as $user)
			<tr data-userid="{{ $user->id }}"><td>{{ $user->name }}</td></tr>
		@empty
			No hay usuarios conectados!
		@endforelse
	</table>
@endsection

@section('footer')
<script>
var textarea;
var users;
var myNick;
var myId;
var tabs;
var chats;
var openChats = new Array(1);
openChats[0] = -1;

function clearText()
{
	textarea.val('');
}

function isOpen(id)
{
	return openChats.indexOf(id) != -1;
}

function createNewTab(id, name)
{
	if (id == myId || isOpen(id))
	{
		return;
	}

	var link = $('<li><a href="#tab' + id + '" id="link' + id + '">' + name + '</a></li>');
	var div = $('<div class="tab-pane" id="tab' + id + '">');
	var table = $('<table id="' + id + '" class="table table-striped table-bordered"></table>');	

	chats.append(link);
	div.append(table);
	tabs.append(div);

	openChats.push(id);

	registerTabs();
}

function insertNewUser(user)
{
	var newUser = createNewUserRow(user.nick, user.id);
	$('#users').append(newUser);
}

function createNewUserRow(name, id)
{
	return $('<tr data-userid="' + id + '"><td>' + name + '</td></tr>');
}

function updateUsers()
{
	var data = {};
	var url = '/chat/users';
	
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			//console.log(data);
			// clear previous
			users.empty();
			$.each(data, function(key, value) {			
				insertNewUser(value.attributes);
			});
			registerTabOpeners();
		});
}

function insertNewMessage(message)
{
	var id;
	if (message.to == -1)
	{
		id = -1;
	} else
	{
		if (message.from != myId)
		{
			id = message.from;
		} else
		{
			id = message.to;
		}
	}

	var newRow = createNewMessageRow(message.nick, message.message, message.id);
	$('#' + id).append(newRow);
}

function createNewMessageRow(nick, message, id)
{
	return $('<tr data-messageid="' + id + '"><td>' + nick + ': ' + message + '</td></tr>');
}

function updateMessages(from)
{
	var data = {};
	var url = '/chat/update';
	
	data['from'] = from;
	data['id'] = $('#' + from + ' tr').last().data('messageid');

	if (!data['id'])
		data['id'] = -1; // default get all

	//console.log(data['id']);
	
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			// console.log(data);
			$.each(data, function(key, value) {			
				insertNewMessage(value);
			});
		});
}

function sendMessage(to, message)
{
	var url = '/chat/message';
	var data = {};
	data['message'] = message;
	data['to'] = to;
	/* instantaneo */
	var tempMessage = {}
	tempMessage['message'] = message;
	tempMessage['nick'] = myNick;
	tempMessage['id'] = $('#' + to + ' tr').last().data('messageid') + 1;
	insertNewMessage(tempMessage);

	$.post(
		url,
		data,
		function() {		
			clearText();
		});
}

function registerTabs()
{
	$('#chats a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})
}

function registerTabOpeners()
{
	$('#users tr').click(function (e) {
		e.preventDefault();
		
		var tr = $(this);
		// check if tab isn't already open
		if (!isOpen(tr.data('userid')))
		{
			createNewTab(tr.data('userid'), tr.children().html());
		}
	});
}

$(document).ready(function($)
{
	// vars
	textarea = $('#texto');
	users = $('#users');
	myNick = '{{ $user->nick }}';
	myId = {{ $user->id }};
	tabs = $('#tabs');
	chats = $('#chats');

	// enable chat tabs
	registerTabs();
	// enable user list clicks
	registerTabOpeners();

	/*
	$('#send').click(function() {
		var url = '/chat/message';
		var m = textarea.val();
		var data = {};
		data['message'] = textarea.val();
		data['to'] = -1;
		$.post(
			url,
			data,
			clearText
			);
	});
*/
	
	$('form').submit(function(e) {
		var form = $(this);
		var data = form.serializeArray()[0];
		var to = $('div.active').attr('id').substr(3);
		// console.log(data);
		var message = data.value;
		sendMessage(to, message);
		e.preventDefault();
	});

	setInterval(function() {
		for (i = 0; i < openChats.length; i++)
		{
			updateMessages(openChats[i]);
		}

		updateUsers();
	}, 2000);
});
</script>
@endsection