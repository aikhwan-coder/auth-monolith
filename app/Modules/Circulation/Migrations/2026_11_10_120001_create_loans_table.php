<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            // Referensi lintas context: hanya ID + snapshot nama (tanpa relasi Eloquent ke Membership)
            $table->unsignedBigInteger('member_id')->index();
            $table->string('member_name');
            $table->string('loan_number')->unique();
            $table->timestamp('borrowed_at')->useCurrent();
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('borrowed'); // borrowed | returned
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
