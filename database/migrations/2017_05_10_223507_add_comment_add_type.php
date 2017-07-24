<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCommentAddType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('yz_comment', function(Blueprint $table)
		{
            if (!Schema::hasColumn('yz_comment', 'type')) {
                $table->tinyInteger('type')->default(3);
            }
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('yz_comment', function(Blueprint $table)
		{
            if (Schema::hasColumn('yz_comment', 'type')) {
                $table->dropColumn('type');
            }
		});
	}

}
