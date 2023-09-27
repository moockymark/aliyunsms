<?php
/**
 * Email: moocky@moocky.net
 * Created by Moocky
 * Date: 2023/8/14
 */

namespace Moocky\Aliyunsms\Providers;

use Illuminate\Support\ServiceProvider;
use Moocky\Aliyunsms\Aliyunsms;
use Moocky\Aliyunsms\Contracts\Aliyunsms as AliyunsmsContract;

class LaravelServiceProvider extends ServiceProvider
{
	/**
	 * 服务提供者加是否延迟加载.
	 * @var bool
	 */
	protected $defer = true; // 延迟加载服务

	public function boot()
	{
		// 发布配置文件到 laravel 的config 下
		$path = realpath(__DIR__ . '/../../config/aliyunsms.php');
    $this->publishes([ $path => config_path('aliyunsms.php')],'config');
    $this->mergeConfigFrom($path, 'aliyunsms');

    $this->commands([
      \Moocky\Aliyunsms\Console\TableCommand::class
    ]);
	}

	public function register()
	{
		// 单例绑定服务
		$this->app->singleton('aliyunsms', function ($app) {
			return new Aliyunsms($app->config['aliyunsms']);
		});
    //使用bind绑定实例到接口以便依赖注入
    $this->app->bind(AliyunsmsContract::class,function($app){
			return new Aliyunsms($app->config['aliyunsms']);
    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		// 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
		return ['aliyunsms'];
	}
}
