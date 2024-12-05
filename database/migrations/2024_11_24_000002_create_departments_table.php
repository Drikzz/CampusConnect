<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Insert departments
        DB::table('departments')->insert([
            ['code' => 'COL', 'name' => 'College of Law', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'COA', 'name' => 'College of Agriculture', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CLA', 'name' => 'College of Liberal Arts', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CA', 'name' => 'College of Architecture', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CN', 'name' => 'College of Nursing', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CAIS', 'name' => 'College of Asian & Islamic Studies', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CCS', 'name' => 'College of Computing Studies', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CFES', 'name' => 'College of Forestry & Environmental Studies', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CCJE', 'name' => 'College of Criminal Justice Education', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CHE', 'name' => 'College of Home Economics', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'COE', 'name' => 'College of Engineering', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'COM', 'name' => 'College of Medicine', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CPADS', 'name' => 'College of Public Administration & Development Studies', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CSSPE', 'name' => 'College of Sports Science & Physical Education', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CSM', 'name' => 'College of Science and Mathematics', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CSWCD', 'name' => 'College of Social Work & Community Development', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CTE', 'name' => 'College of Teacher Education', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};