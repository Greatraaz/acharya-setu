<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_super')->default(false);
            $table->json('modules')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->nullable()->after('role')->constrained('admin_roles')->nullOnDelete();
        });

        $roleId = DB::table('admin_roles')->insertGetId([
            'name' => 'Super Admin',
            'is_super' => true,
            'modules' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('role', 'admin')->update(['admin_role_id' => $roleId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_role_id');
        });
        Schema::dropIfExists('admin_roles');
    }
};
