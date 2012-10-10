@layout('larachat::layouts.main')

@section('main-chat')
<div class="row-fluid">
	<div class="span12 well">
		<table id="-1" class="table table-striped table-bordered">
			@forelse ($global_messages as $message)
				<tr data-messageid="{{ $message->id }}"><td>{{ $message->nick }}: {{ $message->message }}</td></tr>
			@empty
				No hay mensajes!
			@endforelse
		</table>
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
	<table class="table table-striped table-bordered">
		@forelse ($online_users as $user)
			<tr><td>{{ $user->name }}</td></tr>
		@empty
			No hay usuarios conectados!
		@endforelse
	</table>
@endsection

@section('footer')
<script>
var textarea = $('#texto');
function clearText()
{
	textarea.val('');
}

function insertNewMessage(message)
{
	var newRow = createNewMessageRow(message.nick, message.message, message.id);
	$('#-1').append(newRow);
}

function createNewMessageRow(nick, message, id)
{
	return $('<tr data-messageid="' + id + '"><td>' + nick + ': ' + message + '</td></tr>');
}

function updateMessages(from)
{
	var data = {};
	var url = '/chat/update';
	// HACK: get all
	data['from'] = from;
	data['id'] = $('#' + from + ' tr').last().data('messageid');
	
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			//console.log(data);
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
	$.post(
		url,
		data,
		clearText
		);
}

$(document).ready(function($)
{
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
		console.log(data);
		var message = data.value;
		sendMessage(form.data('to'), message);
		e.preventDefault();
	});

	setInterval(function() {
		updateMessages(-1);
	}, 2000);
});
</script>
@endsection