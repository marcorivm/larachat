<?php

class Larachat_Chat_Controller extends Base_Controller {

	/**
	 * Chat action, gets the logged in user and generates the view
	 * @return View The chat view
	 */
	public function action_chat()
	{
		// Get logged in user
		$user = Auth::user();

		if (!$user)
		{
			return 'Invalid User';
		}
		// Currently the nickname is the same as the name
		$user->nick = $user->name;

		// Store nick in cache
		Larachat\Models\User::addNick($user->id, $user->name);
		Larachat\Models\User::updateTimestamp($user->id);

		// Attach objects to view
		$this->view_opts['user'] = $user;
		$this->view_opts['online_users'] = Larachat\Models\User::getOnlineUsers();
		$this->view_opts['global_messages'] = Larachat\Models\Message::getGlobalMessages();

		// Generate view
		return View::make('larachat::home.index', $this->view_opts);
	}

	/**
	 * Message action, receives data to send a message to a user
	 * @return int The id of the stored message
	 */
	public function action_message()
	{
		// Get current user
		$user = Auth::user();
		// update timestamps
		Larachat\Models\User::updateTimestamp($user->id);

		// Create message with data
		$message = new Larachat\Models\Message;
		$message->from = $user->id;
		$message->to = Input::get('to');
		$message->message = Input::get('message');
		$message->nick = Larachat\Models\User::getNick($user->id);
		// Save to DB
		$message->save();
		// return message->id to client
		return $message->id;
	}

	/**
	 * Get new messages action, retrieves the messages from the GET/POST
	 * parameters looks up messages between the user and the from user starting
	 * from message id
	 * @return Message[] Array with messages in JSON format
	 */
	public function action_getNewMessages()
	{
		// Retrieve data from parameters
		$from = Input::get('from');
		$id = Input::get('id');

		return Larachat\Models\Message::getMessagesFromAfter($from, $id);
	}

	/**
	 * Returns the currently logged in users
	 * @return User[] Array with user objects in JSON
	 */
	public function action_getOnlineUsers()
	{
		// get online users' ids
		$online_users = Larachat\Models\User::getOnlineUsers();
		return Response::json($online_users);
	}

	/**
	 * Returns the name associated with an id
	 * @return string The user's name
	 */
	public function action_getName()
	{
		// Get parameter and lookup user in DB
		$id = Input::get('id');
		$user = \User::find($id);

		if (!$user)
		{
			return '';
		}

		return $user->name;
	}

	/**
	 * Returns the ids of the users who have messaged the logged in user in JSON
	 * @return int[] Array with the users' ids in JSON
	 */
	public function action_getNotification()
	{
		return Response::json(Larachat\Models\User::getUnreadUsers());
	}

	/**
	 * Marks all the messages sent from an id as red
	 */
	public function action_markAsRead()
	{
		$id = Input::get('id');

		Larachat\Models\Message::markAsRead($id);
	}

	/**
	 * Returns the messageid from the last general message
	 * @return int The messageid
	 */
	public function action_lastGeneral()
	{
		return Larachat\Models\Message::lastGeneral();
	}

	// lqai/chat/test
	public function action_generalUpdate()
	{
		$myUser = Auth::user();
		$laraUser = new Larachat\Models\User($myUser, $myUser->name);
		$generalUpdate;
		$lastGeneralID = Input::get('generalID');

		// update my timestamps
		$laraUser->updateTimestamps();

		// return active users
		$generalUpdate['online_users'] = Larachat\Models\User::getOnline();

		// get messages from general chat
		$generalUpdate['generalUnread'] = Larachat\Models\Message::where('to', '=', '-1')
												 ->where('id', '>', $lastGeneralID)
												 ->get();
		
		// get unread messages
		$generalUpdate['privateUnread'] = $laraUser->getPrivateUnread();

		// get open messages
		$generalUpdate['openChats'] = $laraUser->getStoredChatsFromCache();
		
		return Response::json($generalUpdate);
	}

	public function action_test()
	{
		// Get logged in user
		$user = Auth::user();

		if (!$user)
		{
			return 'Invalid User';
		}
		// // Currently the nickname is the same as the name
		// $user->nick = $user->name;

		// // Store nick in cache
		// Larachat\Models\User::addNick($user->id, $user->name);
		// Larachat\Models\User::updateTimestamp($user->id);
		// 
		$laraUser = new Larachat\Models\User($user, $user->name);
		$laraUser->addNickToCache();
		$laraUser->updateTimestamps();

		$user->nick = $user->name;

		// Attach objects to view
		$this->view_opts['user'] = $user;
		$this->view_opts['online_users'] = Larachat\Models\User::getOnlineUsers();
		$this->view_opts['global_messages'] = Larachat\Models\Message::getGlobalMessages();

		//DEBUG
		// return var_dump($laraUser);
		// Generate view
		return View::make('larachat::home.index2', $this->view_opts);
	}

	public function action_markAsReadFromUntilID()
	{
		$myUser = Auth::user();
		$laraUser = new Larachat\Models\User($myUser, $myUser->name);

		$id = Input::get('id');
		$messageid = Input::get('messageid');

		$laraUser->markAsReadFromUntilID($id, $messageid);
	}

	public function action_storeChat()
	{
		$myUser = Auth::user();
		$laraUser = new Larachat\Models\User($myUser, $myUser->name);

		$laraUser->storeChatToCache(Input::get('userID'));

		return Response::json($laraUser->getStoredChatsFromCache());
	}

	public function action_removeChat()
	{
		$myUser = Auth::user();
		$laraUser = new Larachat\Models\User($myUser, $myUser->name);

		$laraUser->removeChatFromCache(Input::get('userID'));

		return Response::json($laraUser->getStoredChatsFromCache());
	}
}

?>