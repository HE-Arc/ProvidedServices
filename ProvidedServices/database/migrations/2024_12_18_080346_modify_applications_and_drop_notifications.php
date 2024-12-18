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
        // Supprimer la clé étrangère de notifications
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_application_id_foreign'); // Supprime la contrainte FK
        });

        // Supprimer la table notifications
        Schema::dropIfExists('notifications');

        // Modifier la table applications
        Schema::table('applications', function (Blueprint $table) {
            // Retirer AUTO_INCREMENT
            \DB::statement('ALTER TABLE applications MODIFY id INT NOT NULL');
            // Supprimer la clé primaire
            $table->dropPrimary();
            // Supprimer la colonne id
            $table->dropColumn('id');
        });

        // Ajouter une clé primaire composite
        Schema::table('applications', function (Blueprint $table) {
            $table->primary(['job_post_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Recréer la table notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('application_id');
            $table->timestamps();

            // Ajouter la clé étrangère
            $table->foreign('application_id')
                  ->references('id')
                  ->on('applications')
                  ->onDelete('cascade');
        });

        // Restaurer la colonne id dans applications
        Schema::table('applications', function (Blueprint $table) {
            $table->id()->first(); // Ajouter id avec AUTO_INCREMENT
            $table->dropPrimary(); // Retirer la clé primaire composite
        });
    }
};
