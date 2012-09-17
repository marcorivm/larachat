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

		$date = Date::forge('now - 3 days'); // Default history 3 hours
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
