<?php

class Larachat_Chat_Controller extends Base_Controller {
	
	public function action_chat()
	{
		$user = Auth::user();
		
		if (!$user)
		{
			return 'Invalid User';
		}

		// Store nick in cache
		Larachat\Models\User::addNick($user->id, $user->name);
		Larachat\Models\User::updateTimestamp($user->id);
		

		$this->view_opts['user'] = $user;
		$this->view_opts['online_users'] = Larachat\Models\User::getOnlineUsers();
		$this->view_opts['global_messages'] = Larachat\Models\Message::getGlobalMessages();

		return View::make('larachat::home.index', $this->view_opts);
	}

	public function action_message()
	{
		$user = Auth::user();
		// update timestamps
		Larachat\Models\User::updateTimestamp($user->id);

		$message = new Larachat\Models\Message;
		$message->from = $user->id;
		$message->to = Input::get('to');
		$message->message = Input::get('message');
		$message->nick = Larachat\Models\User::getNick($user->id);
		$message->save();
		return true;
	}

	public function action_getNewMessages()
	{
		$from = Input::get('from');
		$id = Input::get('id');

		return Larachat\Models\Message::getMessagesFromAfter($from, $id);
	}

	public function action_getOnlineUsers()
	{
		$online_users = Larachat\Models\User::getOnlineUsers();
		return Response::json($online_users);
		$online = array();

		foreach($online_users as $user)
		{
			$temp = User::find($user->id);
			$temp->nick = Larachat\Models\User::getNick($user->id);

			$online[] = $temp;
		}

		//return var_dump($online);
		return \Response::json($online);
	}
}

?>