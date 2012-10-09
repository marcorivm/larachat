<?php

class Larachat_Chat_Controller extends Base_Controller {
	
	public function action_chat()
	{
		$user = Auth::user();

		// poner nick en cache
		Chat::addNick($user->id, $user->name);		
		
		if (!$user)
		{
			return 'Invalid User';
		}

		$this->view_opts['user'] = $user;
		$this->view_opts['online_users'] = Larachat\Models\User::getOnlineUsers();
		$this->view_opts['global_messages'] = Larachat\Models\Message::getGlobalMessages();

		return View::make('larachat::home.index', $this->view_opts);
	}

	public function action_message()
	{
		$user = Auth::user();

		$message = new Larachat\Models\Message;
		$message->from = $user->id;
		$message->to = Input::get('id');
		$message->message = Input::get('message');
		$message->nick = Chat::getNick($user->id);
		$message->save();
		return true;
	}
}

?>