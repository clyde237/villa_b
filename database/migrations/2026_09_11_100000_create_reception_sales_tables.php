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
        Schema::create('reception_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('sale_number', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('cash_register_session_id')->nullable()->constrained('cash_register_sessions')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name')->default('Client');
            $table->string('customer_phone', 50)->nullable();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('room_number', 30)->nullable();
            $table->string('payment_type', 30)->default('immediate'); // immediate, room_charge
            $table->string('payment_method', 30)->nullable(); // cash, card, mobile_money, bank_transfer, room_charge, other
            $table->string('payment_status', 30)->default('paid'); // paid, charged_to_room, refunded
            $table->integer('subtotal')->default(0); // en centimes
            $table->integer('tax_amount')->default(0); // en centimes
            $table->integer('total_amount')->default(0); // en centimes
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('cash_register_session_id');
            $table->index('booking_id');
            $table->index('customer_id');
            $table->index('payment_status');
            $table->index('created_at');
        });

        Schema::create('reception_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_sale_id')->constrained('reception_sales')->cascadeOnDelete();
            $table->foreignId('service_item_id')->nullable()->constrained('service_items')->nullOnDelete();
            $table->string('category', 50)->default('other');
            $table->string('name', 255);
            $table->decimal('quantity', 8, 2)->default(1);
            $table->integer('unit_price')->default(0); // en centimes
            $table->integer('total_price')->default(0); // en centimes
            $table->foreignId('folio_item_id')->nullable()->constrained('folio_items')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('reception_sale_id');
            $table->index('service_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reception_sale_items');
        Schema::dropIfExists('reception_sales');
    }
};
