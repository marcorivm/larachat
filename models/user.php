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

	/**
	 * Adds a nickname to the cache
	 * @param int $id   The user ID
	 * @param string $nick The user's nickname to be stored
	 */
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

	/**
	 * Gets a stored nick in the cache
	 * @param  int $id the User ID
	 * @return string     The user's nickname stored in cache
	 */
	public static function getNick($id)
	{
		// Get currently stored in cache nicknames
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

	/**
	 * Removes a stored nick from cache
	 * @param  int $id The user's id to remove
	 */
	public static function removeNick($id)
	{
		// Create new user array
		$users = \Cache::get('online_users');
		\Cache::forget('online_users');
		$new_users;

		if ($users)
		{
			foreach($users as $user)
			{
				// Only add to new array if the user is different from
				// the specified parameter
				if ($user[0] != $id)
					$new_users[] = $user;
			}
		}
		// Store new array back in cache
		\Cache::forever('online_users', $new_users);
	}

	/**
	 * Updates the timestamps of the specified User id
	 * @param  int $id The user
	 */
	public static function updateTimestamp($id)
	{
		// Find the User object and update its timestamp
		$user = \User::find($id);
		$user->timestamp();
		$user->save();
	}

	/**
	 * Gets the online users' IDs
	 * @return User[] An array with the user objects of the logged on users
	 */
	public static function getOnlineUsers()
	{
		$users = array();

		if (\Cache::has('online_users'))
		{
			// Get active users from cache
			$online_users = \Cache::get('online_users');

			foreach($online_users as $user)
			{
				// Get user object
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
					// If user hasn't been active for the last 5 minutes
					// remove from cache
					static::removeNick($temp->id);
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

	/**
	 * Gets the users' ids from which the current user has unread messages from
	 * @return int[] Array with the users' IDs
	 */
	public static function getUnreadUsers()
	{
		$myId = \Auth::user()->id;

		// Get all unread messages directed to me
		$messages = DB::table('messages')->where('status', '=', 'false')
										 ->where(function($query) use ($myId) {			
			$query->or_where('to', '=', $myId);
		})->get();

		$users = array();

		foreach($messages as $message)
		{
			$users[] = $message->from;
			// $users['nick'] = $message->nick;
		}

		return array_unique($users);
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
