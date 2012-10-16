<?php namespace Larachat\Models;
use Larachat\Libraries\Date;

class Message extends \Eloquent {



	public static function getGlobalMessages()
	{
		$date = Date::forge('now - 1 days'); // Default history 1 day

		return static::where('to', '<', '0')
						->where('created_at', '>=', $date->format('datetime'))
						->get();
	}
	// id
	// to
	// from
	// mensaje
	// timestamps
	// status

	public static function getMessagesFromAfter($from, $id)
	{
		$user = \Auth::user();
		if ($from == -1)
		{
			$messages = \DB::table('messages')->where('to', '=', '-1')
											  ->where('id', '>', $id)
											  ->get();
		} else
		{
			$messages = \DB::table('messages')->where('id', '>', $id)
											  ->where(function($query) use ($from) {
											  	$query->where('to', '=', $from);
											  	$query->or_where('from', '=', $from);
											  })
											  ->where('to', '!=', '-1')
											  ->get();
			static::markAsRead($from);
		}

		return \Response::json($messages);
	}

	public static function lastGeneral()
	{
		$messages = \DB::table('messages')->where('to', '=', '-1')
										  ->get();

		return end($messages)->id;
	}

	public static function markAsRead($from)
	{
		$myId = \Auth::user()->id;

		$affected = \DB::table('messages')->where('from', '=', $from)
										 ->where('to', '=', $myId)
										 ->update(array('status' => true));
	}
}
