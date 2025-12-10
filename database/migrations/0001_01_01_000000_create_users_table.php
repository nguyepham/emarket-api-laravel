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
        Schema::create('users', function (Blueprint $table) {
            // 1. UUID Primary Key
            // Matches the 'HasUuids' trait in your model.
            // Using UUIDs prevents enumeration attacks (guessing user IDs like 1, 2, 3).
            $table->uuid('id')->primary();

            // 2. Identity Fields
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // 3. Optional Fields
            $table->string('phone')->nullable();

            // 4. Role-Based Access Control (RBAC)
            // Matches the 'roles' => 'array' cast in the User model.
            // Default to a standard customer role to ensure data consistency.
            $table->json('roles')->default(json_encode(['role_customer']));

            // 5. Standard Laravel Fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
