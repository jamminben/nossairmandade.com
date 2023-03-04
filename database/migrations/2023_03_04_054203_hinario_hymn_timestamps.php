<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public $tables = ['hinarios', 'hymns', 'hymn_translations'];
    //, 'hymn_notation_translations'

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach($this->tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->timestamps();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach($this->tables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
