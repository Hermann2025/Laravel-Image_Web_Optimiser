<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optimized_images', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('original_name');
            $table->bigInteger('original_size')->nullable();
            $table->bigInteger('optimized_size')->nullable();
            $table->float('compression_ratio')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('format_converted_to')->nullable();
            $table->json('variants')->nullable();
            $table->string('path_original');
            $table->string('path_optimized')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->boolean('downloaded')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optimized_images');
    }
};