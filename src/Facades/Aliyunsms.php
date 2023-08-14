<?php
/**
 * Email: moocky@moocky.net
 * Created by Moocky
 * Date: 2023/8/14
 */

namespace Moocky\Aliyunsms\Facades;

use Illuminate\Support\Facades\Facade;

class Aliyunsms extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'aliyunsms';
	}
}