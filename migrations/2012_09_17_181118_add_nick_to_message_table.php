<?php

class Larachat_Add_Nick_To_Message_Table {

	/**
	 * Make changes to the database.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('messages', function($table) {
			$table->string('nick');
		});
	}

	/**
	 * Revert the changes to the database.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('messages', function($table) {
			$table->drop_column('nick');
		});
	}

}