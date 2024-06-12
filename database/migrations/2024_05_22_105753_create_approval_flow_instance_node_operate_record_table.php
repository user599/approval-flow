<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalFlowInstanceOperateRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_flow_instance_node_operate_record', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("node_id")->comment("所属节点id");
            $table->unsignedBigInteger("instance_id")->comment("实例id");
            $table->unsignedBigInteger("related_member_id")->comment("相关人id");
            $table->unsignedTinyInteger("status")->default(1)->comment("任务状态:");
            $table->dateTime("operate_time")->nullable()->comment("操作时间");
            $table->string("remark")->nullable()->comment("备注");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->comment("审批流实例操作记录表");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_flow_instance_node_operate_record');
    }
}
