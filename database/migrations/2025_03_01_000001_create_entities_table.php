<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla entities
 * 
 * Almacena entidades (terceros/empresas) cargadas masivamente
 * mediante CSV a través de la API.
 */
class CreateEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('entities')) {
            Schema::create('entities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('type_organization_id')->nullable();
                $table->string('name')->nullable();
                $table->unsignedBigInteger('type_document_identification_id')->nullable();
                $table->string('identification_number')->index();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->unsignedBigInteger('municipality_id')->nullable();
                $table->string('address')->nullable();
                $table->string('email')->nullable();
                $table->string('legal_representative')->nullable();
                $table->string('phone')->nullable();
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
        Schema::dropIfExists('entities');
    }
}
