<?php
/**
 * Email: moocky@moocky.net
 * Created by Moocky
 * Date: 2023/8/14
 */

namespace Moocky\Aliyunsms\Contracts;

use \Exception;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Moocky\Aliyunsms\Models\SmsLog;

abstract class Aliyunsms
{
	protected $config;

	public function __construct(?array $config = [])
	{
		$this->config = $config;
	}
  /**
   * 发送验证码
   *
   * @param string $phone 手机号
   * @param string $templateCode 短信模板
   * @param array $templateParam 短信参数
   * @param string $type 短信类型
   * 
   * @return int
   *   true 发送成功
   *   false 发送失败
   *   -1 30秒内已经有验证码
   */
	abstract public function send(string $phone, string $templateCode, $templateParam = null , ?string $type = 'normal');
}
