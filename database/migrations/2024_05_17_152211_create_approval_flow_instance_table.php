<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalFlowInstanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_flow_instance', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("config_id")->comment("配置信息id");
            $table->bigInteger("current_node_id")->comment("当前节点id");
            $table->tinyInteger("allow_withdraw")->default(0)->comment("是否允许撤回【0否 1是】");
            $table->tinyInteger("withdraw_type")->nullable()->comment("撤回类型【1 未进入流程时撤回 2 流程中撤回 3流程结束后撤回");
            $table->tinyInteger("status")->default(\Js3\ApprovalFlow\Model\ApprovalFlowInstance::STATUS_NOT_START)->comment("实例状态【1 未开始 2 进行中 3 已结束 4 撤销 5 拒绝】");
            $table->dateTime("end_time")->nullable()->comment("结束时间");
            $table->string("remark")->nullable()->comment("备注");
            $table->text("form_data")->nullable()->comment("表单数据");
            $table->string("creator_id")->nullable()->comment("创建人");
            $table->string("creator_type")->nullable()->comment("创建人类型");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->comment("审批流实例信息");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_flow_instance');
    }
}
