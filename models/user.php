<?php namespace Larachat\Models;
use Larachat\Libraries\Date;
use Laravel\File;
use Laravel\Session;
use Laravel\Database as DB;
use Larachat\Models\Message;

class User {
	private $user;
	private $user_name;

	public function __construct($user, $user_name = 'name')
	{
		$this->user = $user;
		$this->user_name = $user_name;
	}

	// Adds a nick to the cache
	public static function addNick($id, $nick)
	{
		$users = \Cache::get('online_users');

		if ($users)
		{
			\Cache::forget('online_users');

			// check if nick is already stored
			foreach ($users as $user)
			{
				if ($user[0] == $id)
				{
					$user[1] = $nick;
					\Cache::forever('online_users', $users);
					return;
				}
			}
		}		

		$users[] = array($id, $nick);
		\Cache::forever('online_users', $users);
		return;
	}

	// Gets a nick from the cache
	public static function getNick($id)
	{
		$users = \Cache::get('online_users');

		if ($users)
		{
			foreach($users as $user)
			{
				if ($user[0] == $id)
					return $user[1];
			}
		}

		return null;
	}

	// Remove a user from cache
	public static function removeNick($id)
	{
		$users = \Cache::get('online_users');
		$new_users;

		if ($users)
		{
			foreach($users as $user)
			{
				if ($user[0] != $id)
					$new_users[] = $user;
			}
		}

		return $new_users;
	}

	public static function updateTimestamp($id)
	{
		$user = \User::find($id);
		$user->timestamp();
		$user->save();
	}

	public static function getOnlineUsers()
	{
		$users = array();

		if (\Cache::has('online_users'))
		{
			$online_users = \Cache::get('online_users');

			foreach($online_users as $user)
			{
				$temp = \User::find($user[0]);
				$now = Date::forge();
				$diff = Date::diff($now, $temp->updated_at);			

				// check timestamp for 5 minutes
				if ($diff->i > 5 ||
					$diff->y > 0 ||
					$diff->m > 0 ||
					$diff->d > 0 ||
					$diff->h > 0)
				{
					User::removeNick($temp->id);
				} else
				{
					$temp->nick = $user[1];
					$users[] = $temp;
				}
			}
		}
		
		return $users;
	}

	public function getName()
	{
		$user_name = $this->user_name;
		return $this->user->$user_name;
	}
	public function getOpenChats()
	{
		// TODO: Remove hack
		$chats = Session::get('chats', array(2)); // Testing hack
		$chats_content = array();
		foreach ($chats as $chat) {
			$chats_content[$chat] = $this->messages($chat)->get();
		}
		return $chats_content;
	}

	public function unread($participant = 0)
	{
		// TODO: add participant filter
		// Status 1 = unread
		return $this->incoming()->where_to($this->id)->where_status('1');
	}
	public function messages($arguments)
	{

		$date = Date::forge('now - 3 hours'); // Default history 3 hours
		$own_id = $this->id;
		$query = DB::table('messages')->where(function($query) use ($own_id){
			$query->where_from($own_id);
			$query->or_where('to', '=', $own_id);
		});
		if(is_array($arguments)) {
			// TODO: various arguments
		} else {
			// If only one argument, assume it's the participant id
			$participant = $arguments;
			$query->where(function ($query) use ($participant)
			{
				$query->where_from($participant);
				$query->or_where('to', '=',$participant);
			});
		}
		 $query->where('created_at', '>=', $date->format('datetime'));
		 $query->order_by('created_at', 'asc');
		return $query;
	}

	public function incoming()
	{
		return $this->user->has_many('Larachat\\Models\\Message', 'to');
	}

	public function outgoing()
	{
		return $this->user->has_many('Larachat\\Models\\Message', 'from');
	}

	public function __get($name)
	{
		if($name == 'incoming') {
			return $this->incoming()->get();
		} else if($name == 'outgoing') {
			return $this->outgoing()->get();
		} else {
			return $this->user->$name;
		}
	}

}
