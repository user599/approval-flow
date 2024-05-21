<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalFlowOperateRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_flow_instance_operate_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("instance_id")->comment("实例id");
            $table->unsignedBigInteger("node_id")->comment("节点id");
            $table->unsignedBigInteger("node_operator_id")->comment("节点操作人id");
            $table->tinyInteger("operate_status")->comment("操作状态：【1通过 2拒绝 3撤销】");
            $table->dateTime("operate_time")->nullable()->comment("操作时间");
            $table->text("remark")->nullable()->comment("备注信息");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->comment('系统用户信息');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_flow_instance_operate_record');
    }
}
