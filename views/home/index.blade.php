@layout('larachat::layouts.main')

@section('main-chat')
<div class="row-fluid">
	<div class="span12">
		<table class="table table-striped">
			@forelse ($global_messages as $message)
				<tr><td>{{ $message->nick }}: {{ $message->message }}</td></tr>
			@empty
				No hay mensajes!
			@endforelse
		</table>
	</div>
</div>
<div class="row-fluid">
	<div class="span10">
		<textarea rows="3" id="texto" name="texto" class="span11"></textarea>
	</div>
	<div class="span1">
		<button id="send" class="btn btn-large">Enviar</button>
	</div>
</div>
@endsection

@section('right-bar')
	<table class="table table-striped">
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

$(document).ready(function($)
{
	$('#send').click(function() {
		var message = textarea.val();

		$.post('/chat/message', { message: textarea.val(), to: '-1'});

		clearText();
	});
});
</script>
@endsection