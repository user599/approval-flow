<?php

namespace Js3\ApprovalFlow;


use GuzzleHttp\Client;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use Js3\ApprovalFlow\Console\RelatedApplicationMakeCommand;
use Js3\ApprovalFlow\Encrypter\AesEncrypter;
use Js3\ApprovalFlow\Encrypter\Encrypter;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\RelatedApplication\RelatedApplicationFactory;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 15:33
 */
class ApprovalFlowServiceProvider extends ServiceProvider
{

    /**
     * @explain: 注册服务
     * @author: wzm
     * @date: 2024/5/16 15:59
     * @remark:
     */
    public function register()
    {
        //合并配置文件
        $this->mergeConfigFrom(
            $this->getConfigFilePath(), 'approval-flow'
        );

        //注册服务
        $this->registerProvider();

    }

    /**
     * @explain:引导方法
     * @author: wzm
     * @date: 2024/5/16 15:59
     * @remark:
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Console/stubs/related-application.stub' => base_path('stubs/related-application.stub'),
            ], 'stubs');
            $this->commands([
                RelatedApplicationMakeCommand::class
            ]);
        }

        //发布配置文件
        $this->publishes([
            $this->getConfigFilePath() => config_path('approval-flow.php'),
        ]);

        //注册路由
        $this->loadRoutes();

        //注册数据库迁移
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        //注册基础请求
        $this->app->singleton(HttpClient::class, function () {
            return new HttpClient(
                new Client($this->getConfig("http")),
                $this->app->make(Encrypter::class)
            );
        });

        //为允许数据库迁移使用comment方法
        $this->addCommentTableMethodWhenMigration();

        //注册关联应用
        $this->registerRelatedApplication();

    }

    /**
     * @explain:注册基本服务和配置类中定义的服务
     * @author: wzm
     * @date: 2024/5/16 17:21
     * @remark:
     */
    protected function registerProvider()
    {
        //实例化默认加密方法
        $this->app->singleton(AesEncrypter::class, function () {
            return new AesEncrypter(
                $this->getConfig("aes.key"),
                $this->getConfig("aes.iv")
            );
        });
        //用户可自定义加密类，只要实现了Encrypter
        $this->app->singleton(Encrypter::class, function () {
            return $this->app->make($this->getConfig("provider.encrypter"));
        });
    }


    public function loadRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    /**
     * @explain:队列迁移文件允许添加备注
     * @author: wzm
     * @date: 2024/5/20 9:05
     * @remark:
     */
    protected function addCommentTableMethodWhenMigration()
    {
        Blueprint::macro('comment', function ($comment) {
            if (!Grammar::hasMacro('compileCommentTable')) {
                Grammar::macro('compileCommentTable', function (Blueprint $blueprint, Fluent $command, Connection $connection) {
                    switch ($database_driver = $connection->getDriverName()) {
                        case 'mysql':
                            return 'alter table ' . $this->wrapTable($blueprint) . $this->modifyComment($blueprint, $command);
                        case 'pgsql':
                            return sprintf(
                                'comment on table %s is %s',
                                $this->wrapTable($blueprint),
                                "'" . str_replace("'", "''", $command->comment) . "'"
                            );
                        case 'sqlserver':
                        case 'sqlite':
                        default:
                            throw new Exception("The {$database_driver} not support table comment.");
                    }
                });
            }

            return $this->addCommand('commentTable', compact('comment'));
        });
    }

    /**
     * @explain:注册关联应用生成器
     * @author: wzm
     * @date: 2024/5/20 9:06
     * @remark:
     */
    private function registerRelatedApplication()
    {
        foreach ($this->getConfig("related-application", []) as $slug => $related_application_clazz) {
            RelatedApplicationFactory::register($slug, $related_application_clazz);
        }
    }

    /**
     * @explain: 获取配置信息
     * @param $key
     * @param $default
     * @return mixed
     * @author: wzm
     * @date: 2024/5/20 9:22
     * @remark:
     */
    protected function getConfig($key, $default = null)
    {
        return config("approval-flow." . $key, $default);
    }

    /**
     * @explain:配置文件路径
     * @return string
     * @author: wzm
     * @date: 2024/5/20 9:14
     * @remark:
     */
    private function getConfigFilePath()
    {
        return __DIR__ . '/../config/approval-flow.php';
    }
}
