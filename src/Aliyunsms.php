<?php
/**
 * Email: moocky@moocky.net
 * Created by Moocky
 * Date: 2023/8/14
 */

namespace Moocky\Aliyunsms;

use \Exception;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Moocky\Aliyunsms\Models\SmsLog;

class Aliyunsms
{
	protected $config;

	public function __construct($config)
	{
		$this->config = $config;
	}
  /**
   * 使用AK&SK初始化账号Client
   * @param string $accessKeyId
   * @param string $accessKeySecret
   * @return Dysmsapi Client
   */
  private function createClient(){
    $config = new Config([
      // 必填，您的 AccessKey ID
      "accessKeyId" => $this->config['access_key_id'],
      // 必填，您的 AccessKey Secret
      "accessKeySecret" => $this->config['access_key_secret']
    ]);
    // Endpoint 请参考 https://api.aliyun.com/product/Dysmsapi
    $config->endpoint = $this->config['endpoint'];
    return new Dysmsapi($config);
  }
  /**
   * 创建请求对象
   * @param array $data
   * @return AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest
   */
  private function createRequest(array $data){
    return new SendSmsRequest(array_merge([
      "signName" => $this->config['sign_name']
    ],$data));
  }
  /**
   * 创建运行时配置
   * @param array $options
   * @return AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest
   */
  private function createRuntime(?array $options = []){
    return new RuntimeOptions(array_merge([
      "connectTimeout" => 10000,
      "ignoreSSL" => true
    ],$options));
  }
  /**
   * 获取Model
   * @return Moocky\Aliyunsms\Models\SmsLog
   */
  private function model()
  {
    $model = new SmsLog;
    if($this->config['sms_log_table']){
      $model->setTable($this->config['sms_log_table']);
    }
    return $model;
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
	public function send(string $phone, string $templateCode, $templateParam = null , ?string $type = 'normal')
	{
    // 检查参数
    if(is_string($templateParam)){
      $type = $templateParam;
      $templateParam = '{}';
    }else{
      $templateParam = json_encode($templateParam);
    }
    $client = $this->createClient();
    $request = $this->createRequest([
      "phoneNumbers" => $phone,
      "templateCode" => $templateCode,
      "templateParam" => $templateParam
    ]);
    $runtime = $this->createRuntime();
    try {
      // 复制代码运行请自行打印 API 的返回值
      $resp = $client->sendSmsWithOptions($request, $runtime);
      if($resp->body){
        $this->model()->create([
          'phone' => $phone,
          'sign_name' => $this->config['sign_name'],
          'template_code' => $templateCode,
          'template_param' => $templateParam,
          'type' => $type,
          'code' => $resp->body->code,
          'message' => $resp->body->message,
          'biz_id' => $resp->body->bizId,
          'request_id' => $resp->body->requestId
        ]);
        return (strcasecmp($resp->body->code , 'OK') === 0);
      }
    }catch (Exception $error) {
      throw $error;
      if (!($error instanceof TeaError)) {
        $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
      }
      Log::error($error->message);
    }
    return false;
	}
  /**
   * 发送验证码
   *
   * @param string $phone 手机号
   * @param string $type 验证码类型
   * 
   * @return int
   *   true 发送成功
   *   false 发送失败
   *   -1 30秒内已经有验证码
   */
	public function verification(string $phone , ?string $type = 'verification')
	{
    // 30秒内只能发一次验证码
    if($this->model()->where('phone',$phone)->where('type',$type)->where('verified',0)->where('code','like','OK')->where('created_at','>',Carbon::parse('-30 seconds')->toDateTimeString())->exists()){
      return -1;
    }
    $rand = sprintf("%'.06d\n", rand(1,999999));
    return $this->send($phone,$this->config['template_code'],['rand' => $rand],$type);
	}
  /**
   * 检查验证码
   *
   * @param string $phone 手机号
   * @param string $rand 验证码
   * @param string $type 验证码类型
   * @param int $expires 验证码有效时间，默认为900秒
   * 
   * @return int
   *   1 验证码匹配
   *   0 验证码不匹配
   *   -1 没有找到对应的验证码
   *   -2 验证码已经被验证过
   *   -3 验证码已过期
   */
  public function verify(string $phone, string $rand, ?string $type = 'verification', ?int $expires = 900){
    if($sms = $this->model()->where('phone',$phone)->where('type',$type)->where('code','like','OK')->latest()->first()){
      // 已经被验证
      if($sms->verified){
        return -2;
      }
      if($sms->created_at->getTimestamp() + $expires < time() ){
        return -3;
      }
      if($sms->templateParam && array_key_exists('code',$sms->templateParam) && $sms->templateParam['code'] === $code){
        $sms->update(['verified' => 1]);
        return 1;
      }
      return 0;
    }
    return -1;
  }
}
