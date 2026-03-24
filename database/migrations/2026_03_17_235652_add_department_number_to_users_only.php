<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la columna ya existe
        $hasColumn = DB::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name='users' AND column_name='department_number'
        ");

        if (empty($hasColumn)) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('department_number')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department_number')) {
                $table->dropColumn('department_number');
            }
        });
    }
};