<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assigned_task_list_admin', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('deadline');
            $table->enum('priority', Task::$priorities);
            $table->string('status')->default('Pending');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('user_id'); 
            $table->foreign('admin_id')->references('id')->on('users');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_task_list_admin');
    }
};
