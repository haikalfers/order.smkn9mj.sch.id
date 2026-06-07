<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', [
                'pending', 'design_process', 'design_done',
                'production', 'done', 'delivered', 'cancelled'
            ])->default('pending');
            $table->date('deadline')->nullable();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('payment_status')->default('unpaid'); // unpaid, dp, paid
            $table->decimal('dp_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
