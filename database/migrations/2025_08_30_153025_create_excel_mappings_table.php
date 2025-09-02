<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id'); // reference to uploaded file
            $table->string('sheet_name');
            $table->string('table_name');
            $table->json('column_mappings'); // {"SheetColumn":"DBColumn"}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_mappings');
    }
};