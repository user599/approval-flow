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
            $table->bigInteger("current_node_id")->comment("当前节点id");
            $table->tinyInteger("can_revocation")->default(0)->comment("是否允许撤回【0否 1是】");
            $table->tinyInteger("revocation_type")->comment("撤回类型【1 未进入流程时撤回 2 流程中撤回 3流程结束后撤回");
            $table->string("creator_id")->nullable()->comment("创建人");
            $table->string("creator_type")->nullable()->comment("创建人类型");
            $table->dateTime("finish_time")->comment("完成时间");
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
