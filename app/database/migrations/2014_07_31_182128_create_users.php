<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration {


	public function up()
	{
		Schema::create('users', function($table)
        {
        	$table->increments('id');
            $table->string('node_id', 40)->unique();
            $table->string('node_string');
            $table->timestamps();
        });
	}


	public function down()
	{
		Schema::drop('users');
	}


}
