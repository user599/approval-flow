<?php


namespace Js3\ApprovalFlow\Controller;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 15:26
 */
class ApprovalFlowApplicationController extends Controller
{


    public function getApplicationInfo($slug)
    {
        return $slug;

    }

    public function getApplicationChildren($slug)
    {
        return $slug . "--children";
    }

}