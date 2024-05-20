<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalFlowInstanceNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_flow_instance_node', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("parent_id")->comment("父节点id");
            $table->unsignedBigInteger("instance_id")->comment("实例id");
            $table->string("name",50)->comment("节点名称");
            $table->string("node_type")->comment("节点类型");
            $table->dateTime("pass_time")->nullable()->comment("节点通过时间");
            $table->text("metadata")->nullable()->comment("节点元数据，存储一些额外信息");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->comment("审批流实例节点信息");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_flow_instance_node');
    }
}