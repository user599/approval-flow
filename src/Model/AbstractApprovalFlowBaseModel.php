<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/22 14:23
 */
class AbstractApprovalFlowBaseModel extends Model
{
    use SoftDeletes;

    public function getConnectionName()
    {
        return config("approval-flow.db.connection");
    }


}
