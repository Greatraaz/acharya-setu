<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
    
            // Owner of transaction — always a User (any role)
            $table->unsignedBigInteger('user_id');
    
            $table->enum('type', ['credit', 'debit', 'refund', 'transfer_in', 'transfer_out']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->string('reference')->nullable()->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
    
            // Self-ref for transfer pairs
            $table->unsignedBigInteger('transfer_pair_id')->nullable();
    
            // Linked source entity (Order, Session, etc.)
            $table->string('transactionable_type')->nullable();
            $table->unsignedBigInteger('transactionable_id')->nullable();
    
            // Which user (admin) performed this manually
            $table->unsignedBigInteger('performed_by')->nullable();
    
            $table->json('meta')->nullable();
            $table->timestamps();
    
            $table->index('user_id');
            $table->index(['transactionable_type', 'transactionable_id'], 'wallet_txn_transactionable_index');
            $table->index('reference');
            $table->index('created_at');
        });
    
        // FKs after table creation
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
    
            $table->foreign('performed_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
    
            $table->foreign('transfer_pair_id')
                  ->references('id')->on('wallet_transactions')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['performed_by']);
            $table->dropForeign(['transfer_pair_id']);
        });
        Schema::dropIfExists('wallet_transactions');
    }
};
