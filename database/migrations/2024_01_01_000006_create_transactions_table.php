<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->enum('source', ['views', 'upload_fee', 'withdrawal', 'deposit']);
            $table->decimal('amount', 10, 2);
            $table->string('reference')->unique();
            $table->string('payment_method')->nullable(); // flutterwave, etc.
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
