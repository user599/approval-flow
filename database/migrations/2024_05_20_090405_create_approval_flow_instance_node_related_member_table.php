<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalFlowInstanceNodeRelatedMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_flow_instance_node_related_member', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("node_id")->comment("所属节点id");
            $table->unsignedBigInteger("instance_id")->comment("实例id");
            $table->unsignedBigInteger("member_id")->comment("人员id");
            $table->string("member_type")->comment("人员类型");
            $table->unsignedTinyInteger("status")->comment("状态,");
            $table->dateTime("operate_time")->nullable()->comment("操作时间");
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->comment("审批流实例节点相关人信息");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_flow_instance_node_related_member');
    }
}
