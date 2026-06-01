<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('catatan');
        });

        DB::table('loans')
            ->whereNotNull('deleted_at')
            ->update([
                'returned_at' => DB::raw('deleted_at'),
                'deleted_at' => null,
            ]);
    }

    public function down()
    {
        DB::table('loans')
            ->whereNotNull('returned_at')
            ->update(['deleted_at' => DB::raw('returned_at')]);

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('returned_at');
        });
    }
};
