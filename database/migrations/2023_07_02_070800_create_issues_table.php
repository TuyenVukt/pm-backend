<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('start_time');
            $table->date('end_time');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('milestone_id');
            $table->decimal('estimate_time', 5, 1)->change();
            $table->decimal('actual_time', 5, 1)->change();
            $table->unsignedInteger('before_task_id');
            $table->unsignedInteger('after_task_id');
            $table->unsignedBigInteger('created_user_id');
            $table->unsignedBigInteger('asignee_id');
            $table->string('status');
            $table->string('category_id');
            $table->string('priority');
            $table->boolean('is_parent');
            $table->boolean('is_child');
            $table->timestamps();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('milestone_id')->references('id')->on('milestones')->onDelete('cascade');
            $table->foreign('created_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issues');
    }
}
