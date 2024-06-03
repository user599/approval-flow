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
            $table->unsignedBigInteger("instance_id")->comment("实例id");
            $table->unsignedBigInteger("parent_id")->nullable()->comment("父节点id");
            $table->string("name",50)->comment("节点名称");
            $table->string("type")->comment("节点类型");
            $table->unsignedTinyInteger("status")->comment("节点状态【 0未操作 1通过 】");
            $table->text("metadata")->nullable()->comment("节点元数据，存储一些额外信息");
            $table->dateTime("pass_time")->nullable()->comment("节点通过时间");
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
