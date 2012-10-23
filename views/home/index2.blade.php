@layout('larachat::layouts.main')

@section('main-chat')
<div class="row-fluid">
	<div class="span12 well">
		<ul class="nav nav-tabs" id="chats">
			<li class="active"><a href="#tab-1" id="link-1">General <span id="not-1"></span></a></li>			
		</ul>
		<div class="tab-content" id="tabs" style="overflow: auto; height: 400px;">
			<div class="tab-pane active" id="tab-1">
				<table id="-1" class="table table-striped table-bordered">
					@forelse ($global_messages as $message)
						<tr data-messageid="{{ $message->id }}"><td>{{ $message->nick }}: {{ $message->message }}</td></tr>
					@empty						
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
		@forelse ($online_users as $u)
			<tr data-userid="{{ $u->id }}" style="cursor: pointer"><td>{{ $u->name }}</td></tr>
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
var lastGeneral;

/**
 * Clears the input
 */
function clearText()
{
	// clears the value of the input
	textarea.val('');
}

function scrollBottom () {
	tabs = $('#tabs');
	tabs.scrollTop(tabs.height());
}

/**
 * Returns the user id for the active chat window
 * @return {int} The id for the active chat
 */
function getActiveChatId()
{
	// ID is stored in the active div, id="tabID"
	return $('div.active').attr('id').substr(3);
}

/**
 * Gets the username for the id passed as parameter
 * @param  {int} id The id for the name wanted
 * @return {string}    The name associated with the id
 */
function getUser(id)
{
	var name;
	var data = {};
	data['id'] = id;
	// Sends ajax request to retrieve the name
	$.ajax({
		url: '/chat/name',
		data: data,
		async: false,
		success: function(data, textStatus, xhr) { name = data }
	});

	return name;
}

/**
 * Checks if a chat for an id is currently open (tabbed)
 * @param  {int}  id The id for the user wanted to check
 * @return {Boolean}    True if there's a tab open for that user
 */
function isOpen(id)
{
	// checks if id exists in openChats array
	return openChats.indexOf(id) != -1;
}

/**
 * Marks all messages from a specific user as read
 * @param  {int} id The user ID for which to mark messages as read
 */
function markAsRead (id) {
	var data = {};
	var url = '/chat/read';
	data['id'] = id;
	// sends async post request
	// on success clears the notification mark from the tab
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			// clearNotification(id);
		});
}

function markAsReadFromUntilID (id, messageid) {
	var data = {};
	var url = '/chat/readFromUntilID';
	data['id'] = id;
	data['messageid'] = messageid;
	// sends async post request
	// on success clears the notification mark from the tab
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			// clearNotification(id);
		});
}

/**
 * Notifies that a new message has arrived from a certain user
 * @param  {int} id The id of the user that sent the message
 */
function notify(id, name)
{
	// create new tab
	createNewTab(id, name);
	// place an icon in the span located in the tab
	// <span id="notID"></span>
	var span = $('#not' + id);
	span.empty();
	span.html('<i class="icon-exclamation-sign"></i>');
}

/**
 * Removes the notification icon from the tab of a certain user
 * @param  {int} id The id from which the notification wants to be cleared
 */
function clearNotification(id)
{
	// find the span, and empty it
	var span = $('#not' + id);
	span.empty();
}

/**
 * Gets any notifications (new messages from other users)
 */
function getNotifications()
{
	var data = {};
	var url = '/chat/notification';
	// sends ASYNC post request
	// on success notifies for each user id received	
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {						
			$.each(data, function(key, value) {			
				//console.log(value);
				notify(value);
			});
		});	
}

/**
 * Creates a new tab from a user id
 * @param  {int} id The user id
 */
function createNewT(id)
{
	// get username first, then call creator function
	createNewTab(id, getUser(id));
}

/**
 * Creates a new chat tab
 * @param  {int} id   The user id
 * @param  {string} name The nickname
 */
function createNewTab(id, name)
{
	// don't open a new tab if it's already open
	if (id == myId || isOpen(id))
	{
		return;
	}

	// create HTML elements needed
	var link = $('<li><a href="#tab' + id + '" id="link' + id + '">' + name + ' <span id="not' + id + '"></span></a></li>');
	var div = $('<div class="tab-pane" id="tab' + id + '">');
	var table = $('<table id="' + id + '" class="table table-striped table-bordered"></table>');	
	// append them where apprpopiate
	chats.append(link);
	div.append(table);
	tabs.append(div);
	// add id to active chat
	openChats.push(id);
	// registar click handlers
	registerTabs();
}

/**
 * Inserts a new user to user list
 * @param  {user} user User object to insert
 */
function insertNewUser(user)
{
	var newUser = createNewUserRow(user.nick, user.id);
	$('#users').append(newUser);
}

/**
 * Creates a new HTML row from the data
 * @param  {string} name User nickname
 * @param  {int} id   User id
 * @return {$}      jQuery object pointing to the element
 */
function createNewUserRow(name, id)
{
	return $('<tr data-userid="' + id + '" style="cursor: pointer"><td>' + name + '</td></tr>');
}

/**
 * Sends request and updates users as necessary
 */
function updateUsers()
{
	var data = {};
	var url = '/chat/users';
	// Sends ASYNC request and updates on success
	// also registers click handlers
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			// console.log(data);
			// clear previous
			users.empty();
			$.each(data, function(key, value) {			
				insertNewUser(value.attributes);
			});
			registerTabOpeners();
		});
}

/**
 * Insertas a new message from a user in its respective tab
 * @param  {message} message The message to insert
 * @param  {int} userid  The user id
 * @return {$}         jQuery object representing the meessage row
 */
function insertNewMessageFrom(message, userid)
{
	// create HTML element
	var newRow = createNewMessageRow(message.nick, message.message, message.id);
	// append to respective ID chat tab
	$('#' + userid).append(newRow);

	return newRow;
}

/**
 * Creates new HTML element with data
 * @param  {string} nick    User nickname
 * @param  {string} message The message to print
 * @param  {int} id      The sender's ID
 * @return {$}         jQuery object with the tr
 */
function createNewMessageRow(nick, message, id)
{
	// create new HTML element
	return $('<tr data-messageid="' + id + '"><td>' + nick + ': ' + message + '</td></tr>');
}

/**
 * Sends request and inserts messages to respective tab
 * @param  {int} from The user from which to get new messages
 */
function updateMessages(from)
{
	var data = {};
	var url = '/chat/update';
	// get last message displayed in chat tab
	data['from'] = from;
	data['id'] = $('#' + from + ' tr').last().data('messageid');

	// TODO: change if you want complete history
	if (!data['id'])
		data['id'] = -1; // default get all

	// DEBUG
	// console.log(data['id']);
	// console.log('Updating messages from: ' + data['from'] + ', mid = ' + data['id']);
	
	$.post(
		url,
		data,
		function(data, textStatus, xhr) {
			// console.log(data);
			$.each(data, function(key, value) {			
				insertNewMessageFrom(value, from);
			});
		});
}

/**
 * Sends a message to a user via POST
 * @param  {int} to      The destination user ID
 * @param  {string} message The message to send
 */
function sendMessage(to, message)
{
	// don't send an empty message
	if (message.length == 0)
	{
		return;
	}

	var url = '/chat/message';
	var data = {};
	data['message'] = message;
	data['to'] = to;
	// if message was sent to general, update lastGeneral
	if (to == -1)
	{
		lastGeneral++;
	}
	// Instantly insert message, without waiting for request to complete
	// real messageid will be updated on success
	var tempMessage = {}
	tempMessage['message'] = message;
	tempMessage['nick'] = myNick;
	tempMessage['id'] = $('#' + to + ' tr').last().data('messageid') + 1;
	// this message was created, not fetched from server
	var tr = insertNewMessageFrom(tempMessage, to);
	clearText();

	$.post(
		url,
		data,
		function(data, textStatus, xhr) {			
			// Updates messageid on success
			tr.data('messageid', data);
			// console.log(tr);
		});
	scrollBottom();
}

/**
 * Register click hanlders for tabs
 */
function registerTabs()
{
	$('#chats a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	  id = $(this).attr('id').substring(4);
	  // when user clicks on tab, mark messages as read
	  markAsRead(id);
	  clearNotification(id);
	})
}

/**
 * Register click handlers for users in user list
 */
function registerTabOpeners()
{
	$('#users tr').click(function (e) {
		e.preventDefault();
		
		var tr = $(this);
		// check if tab isn't already open
		// REDUNDANT check
		if (!isOpen(tr.data('userid')))
		{
			createNewTab(tr.data('userid'), tr.children().html());			
		}
		// open tab
		$('#chats a[href="#tab' + tr.data('userid') + '"]').tab('show');
	});
}

/**
 * Function to run on page load
 */
$(document).ready(function($)
{
	// vars
	textarea = $('#texto');
	users = $('#users');
	myNick = '{{ $user->nick }}';
	myId = {{ $user->id }};
	tabs = $('#tabs');
	chats = $('#chats');

	// Gets lastGeneral ID to avoid redundant message fetching
	$.post(
		'/chat/lastGeneral',
		{},
		function(data, textStatus, xhr) {
			lastGeneral = data;
		});



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
	// Submits message on input
	$('form').submit(function(e) {
		var form = $(this);
		var data = form.serializeArray()[0];
		var to = getActiveChatId();
		// console.log(data);
		var message = data.value;
		sendMessage(to, message);
		e.preventDefault();
	});

	// repeat every 3.5 secs, lower times may cause repeated message insertions
	setInterval(function() {
		var input = {};
		input['generalID'] = lastGeneral;		

		$.post(
			'/chat/generalUpdate',
			input,
			function(data, textStatus, xhr) {
			
				// update users	
				users.empty()
				$.each(data.online_users, function(key, value) {
					var temp = value.attributes;
					insertNewUser(temp);
				});
				registerTabOpeners();
				
				// insert new general messages
				$.each(data.generalUnread, function(key, value) {
					var temp = value.attributes;
					lastGeneral = temp.id;
					insertNewMessageFrom(temp, -1);
					notify(-1, 'General');
					scrollBottom();
				});
				
				// insert new private messages				
				$.each(data.privateUnread, function(key, value) {
					var temp = value.attributes;
					var otherID = (myId == temp.from) ? temp.to : temp.from;					
					var otherName = temp.nick;
					notify(otherID * 1, otherName)
					insertNewMessageFrom(temp, otherID);
					//markAsRead(getActiveChatId());	
					markAsReadFromUntilID(otherID, temp.id);	
					
				});
			});
			clearNotification(getActiveChatId());	
	}, 2000);
});
</script>
@endsection