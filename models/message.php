<?php namespace Larachat\Models;
class Message extends \Eloquent {

	public static function getGlobalMessages()
	{
		return \DB::table('messages')->where('to', '<', '0')->get();
	}
	// id
	// to
	// from
	// mensaje
	// timestamps
	// status
}
