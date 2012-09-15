<?php namespace Larachat\Models;

use Laravel\File;
use Laravel\Session;
use Laravel\Database as DB;

class User {
	public $user;

	public function __construct($user)
	{
		$this->user = $user;
	}

	public function getOpenChats()
	{
		// TODO: Remove hack
		$chats = Session::get('chats', array('3')); // Testing hack
		$chats_content = array();
		foreach ($chats as $chat) {
			$chats_content[$chat] = $this->messages($chat)->get();
		}
		return $chats_content;
	}

	public function unread($participant = 0)
	{
		// Status 1 = unread
		return $this->incoming()->where_to($this->id)->where_status('1');
	}
	public function messages($arguments)
	{

		$date = Date::forge('now - 3 hours'); // Default history 3 hours
		$query = DB::table('messages')->where(function($query) {
			$query->where_from($this->id);
			$query->or_where_to($this->id);
		});
		if(is_array($arguments)) {
			// TODO: various arguments
		} else {
			// If only one argument, assume it's the participant id
			$participant = $arguments;
			$query->where(function ($query) use $participant
			{
				$query->where_from($participant);
				$query->or_where_to($participant);
			});
		}
		 $query->where('created_at', '>=', $date);
		 $query->order_by('created_at', 'asc');
		return $query;
	}

	public function incoming()
	{
		return $this->user->has_many('Messages', 'to');
	}

	public function outgoing()
	{
		return $this->user->has_many('Messages', 'from');
	}

	public function __get($name)
	{
		return $this->user->$name;
	}

}
