<?php namespace Larachat\Models;
use Larachat\Libraries\Date;

class Message extends \Eloquent {


	/**
	 * Returns general chat messages at most 1 day old
	 * @return [type] [description]
	 */
	public static function getGlobalMessages()
	{
		$date = Date::forge('now - 1 days'); // Default history 1 day

		return static::where('to', '<', '0')
						->where('created_at', '>=', $date->format('datetime'))
						->get();
	}

	/**
	 * Gets messages from a user after a specified message id
	 * @param  int $from The other user's id
	 * @param  int $id   The last message id the client has
	 * @return message[]       Array with resulting messages
	 */
	public static function getMessagesFromAfter($from, $id)
	{
		$user = \Auth::user();
		if ($from == -1)
		{
			$messages = \DB::table('messages')->where('to', '=', '-1')
											  ->where('id', '>', $id)
											  ->get();
			$messages2 = array();
		} else
		{
			$messages = \DB::table('messages')->where('id', '>', $id)
											  ->where('to', '=', $from)
											  ->where('from', '=', $user->id)
											  ->get();

			$messages2 = \DB::table('messages')->where('id', '>', $id)
											  ->where('to', '=', $user->id)
											  ->where('from', '=', $from)
											  ->get();							  		
			// Client should be the one marking them read
			// static::markAsRead($from);
		}

		return \Response::json($messages + $messages2);
	}

	/**
	 * Gets the last general message's ID
	 * @return int The message ID
	 */
	public static function lastGeneral()
	{
		// Messagess addressed to -1 are general
		$messages = static::where('to', '=', '-1')->get();

		return end($messages)->id;
	}

	/**
	 * Marks all messages from a specified user as read
	 * @param  int $from The user ID to mark as read
	 */
	public static function markAsRead($from)
	{
		$myId = \Auth::user()->id;

		$affected = static::where('from', '=', $from)
										 ->where('to', '=', $myId)
										 ->update(array('status' => true));
	}

}
