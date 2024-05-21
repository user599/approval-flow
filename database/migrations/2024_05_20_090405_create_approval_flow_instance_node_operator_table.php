<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalFlowInstanceNodeOperatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_flow_instance_node_operator', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("node_id")->comment("所属节点id");
            $table->unsignedBigInteger("instance_id")->comment("实例id");
            $table->unsignedBigInteger("operator_id")->comment("操作人id");
            $table->string("operator_type")->comment("操作人类型");
            $table->tinyInteger("operate_status")->default(\Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator::STATUS_UN_OPERATE)->comment("操作状态【0未操作 1通过 2拒绝】");
            $table->dateTime("operate_time")->nullable()->comment("操作时间");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->comment("审批流实例节点操作人信息");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_instance_flow_node_operator');
    }
}
