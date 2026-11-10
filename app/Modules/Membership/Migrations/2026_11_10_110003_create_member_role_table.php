<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_role', function (Blueprint $table) {
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('membership_role_id')->constrained('membership_roles')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->primary(['member_id', 'membership_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_role');
    }
};
