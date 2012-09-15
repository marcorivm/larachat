<?php

class Larachat_Add_Messages_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('messages', function($table) {
			$table->create();
			$table->increments('id');
			$table->integer('from');
			$table->integer('to');
			$table->integer('status');
			$table->string('message');
			$table->timestamps();
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messages');
	}

}