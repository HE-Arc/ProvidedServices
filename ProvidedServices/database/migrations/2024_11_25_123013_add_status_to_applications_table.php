<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Ajouter la colonne 'status' après 'provider_id'
        Schema::table('applications', function (Blueprint $table) {
            $table->enum('status', ['on hold', 'accepted', 'refused'])->default('on hold')->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Supprimer la colonne 'status' en cas de rollback
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
