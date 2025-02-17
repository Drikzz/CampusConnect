
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Insert default trade methods
        DB::table('trade_methods')->insert([
            ['name' => 'Sell Only'],
            ['name' => 'Trade Only'],
            ['name' => 'Both']
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_methods');
    }
};
