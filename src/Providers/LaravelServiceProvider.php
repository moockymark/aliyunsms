<?php
/**
 * Email: moocky@moocky.net
 * Created by Moocky
 * Date: 2023/8/14
 */

namespace Moocky\Aliyunsms\Providers;

use Illuminate\Support\ServiceProvider;
use Moocky\Aliyunsms\Aliyunsms;

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
	}

	public function register()
	{
		// 单例绑定服务
		$this->app->singleton('aliyunsms', function ($app) {
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
