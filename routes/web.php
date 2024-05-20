<?php
/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 15:24
 */

use Illuminate\Support\Facades\Route;

/**
 * 审批流相关控制器
 */
Route::prefix("api/approval-flow")->middleware(["api"])->group(function () {

    /**
     * 关联应用相关
     */
    Route::prefix("relate-application")->group(function() {
        Route::get("{slug}","\Js3\ApprovalFlow\Controller\ApprovalFlowRelateApplicationController@getRelateApplicationOptions");
        Route::get("{slug}/{id}","\Js3\ApprovalFlow\Controller\ApprovalFlowRelateApplicationController@getRelateApplicationChildren");

    });


});
