<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateTransactionsTable.
 */
class CreateTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('payer')->unsigned();
			$table->integer('payee')->unsigned();
			
			$table->foreign('payer')->references('id')->on('persons');
			$table->foreign('payee')->references('id')->on('persons');

            $table->integer('value');
            $table->enum('status', ['TRANSACTION_OK', 'TRANSACTION_NOK']);
            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transactions');
	}
}
