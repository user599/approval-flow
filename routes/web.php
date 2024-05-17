<?php
/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 15:24
 */

use Illuminate\Support\Facades\Route;

Route::prefix("api/approval-flow")->group(function () {

    Route::get("application/{slug}","\Js3\ApprovalFlow\Controller\ApprovalFlowApplicationController@getApplicationInfo");
    Route::get("application/{slug}/children","\Js3\ApprovalFlow\Controller\ApprovalFlowApplicationController@getApplicationChildren");

});