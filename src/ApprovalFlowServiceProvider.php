<?php

namespace Js3\ApprovalFlow;


use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Js3\ApprovalFlow\Encrypter\AesEncrypter;
use Js3\ApprovalFlow\Encrypter\Encrypter;
use Js3\ApprovalFlow\Generators\RelateApplicationFactory;
use Js3\ApprovalFlow\HttpClient\HttpClient;

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
        $this->mergeConfigFrom(
            $this->getConfigFilePath(), 'approval-flow'
        );

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
        //发布配置问及那
        $this->publishes([
            $this->getConfigFilePath() => config_path('approval-flow.php'),
        ]);

        //注册路由
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        //注册数据库迁移
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        //注册基础请求
        $this->app->singleton(HttpClient::class, function () {
            return new HttpClient(
                new Client($this->getConfig("http")),
                $this->app->make(Encrypter::class)
            );
        });

        $this->registerCreator();

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
        //用户可自定义加密类，只要实现了
        $this->app->singleton(Encrypter::class, function () {
            return $this->app->make($this->getConfig("provider.encrypter"));
        });
    }

    protected function getConfig($key, $default = null)
    {
        return config("approval-flow." . $key, $default);
    }

    private function registerCreator() {
        foreach ($this->getConfig("generator",[]) as $slug => $generator_clazz) {
            RelateApplicationFactory::register($slug,$generator_clazz);
        }
    }

    private function getConfigFilePath()
    {
        return __DIR__ . '/../config/approval-flow.php';
    }
}
