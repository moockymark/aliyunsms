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

use Moocky\Aliyunsms\Contracts\Aliyunsms as AliyunsmsContract;
use Moocky\Aliyunsms\Models\SmsLog;
use Moocky\Aliyunsms\ResultData;

class Aliyunsms extends AliyunsmsContract
{
  /**
   * @var array [
   *   'access_key_id' => string,
   *   'access_key_secret' => string,
   *   'endpoint' => string,
   *   'sign_name' => string,
   *   'sms_log_table' => string,
   *   'template_code' => string,
   * ]
   */
	protected $config;

	public function __construct(?array $config = [])
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
    if($this->config['sms_log_table']){
      $model = new SmsLog;
      $model->setTable($this->config['sms_log_table']);
      return $model;
    }
  }
  /**
   * 记录日志
   * @param string $phone 手机号码
   * @param string $templateCode 模板名称
   * @param array $templateParam 内容参数
   * @param string $type 短信类型
   * @param \Moocky\Aliyunsms\ResultData $result 阿里云响应
   * @return Moocky\Aliyunsms\Models\SmsLog
   */
  private function log(string $phone, string $templateCode, ?array $templateParam = null, string $type, ResultData $result){
    if($model = $this->model()){
      return $model->create([
        'sign_name' => $this->config['sign_name'],
        'phone' => $phone,
        'template_code' => $templateCode,
        'template_param' => $templateParam,
        'type' => $type,
        'code' => $result->code,
        'message' => $result->message,
        'biz_id' => $result->bizId,
        'request_id' => $result->requestId
      ]);
    }
  }
  /**
   * 获取日志
   * @param string $phone 手机号码
   * @param string $type 短信类型
   * @return Moocky\Aliyunsms\Models\SmsLog
   */
  private function info(string $phone,?string $type = 'normal'){
    if($model = $this->model()){
      return $model->where('phone',$phone)->where('type',$type)->where('code','like','OK')->latest()->first();
    }
  }
  /**
   * 发送验证码
   *
   * @param string $phone 手机号
   * @param string $templateCode 短信模板
   * @param array $templateParam 短信参数
   * @param string $type 短信类型
   * 
   * @return \Moocky\Aliyunsms\ResultData
   */
	public function send(string $phone, string $templateCode, $templateParam = null, ?string $type = 'normal')
	{
    // 检查参数
    if(is_string($templateParam)){
      $type = $templateParam;
      $templateParam = null;
    }else{
      $templateParam = (array)$templateParam;
    }
    $client = $this->createClient();
    $request = $this->createRequest([
      "phoneNumbers" => $phone,
      "templateCode" => $templateCode,
      "templateParam" => $templateParam ? json_encode($templateParam) : null
    ]);
    $runtime = $this->createRuntime();
    try {
      // 复制代码运行请自行打印 API 的返回值
      $resp = $client->sendSmsWithOptions($request, $runtime);
      if($resp->body){
        $result = ResultData::resp($resp->body);
        $this->log($phone, $templateCode, $templateParam, $type,$result);
        return $result;
      }
    }catch (Exception $error) {
      if (!($error instanceof TeaError)) {
        $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
      }
      Log::error($error->message);
      return new ResultData('RUNTIME_ERROR','运行时错误，请查看日志');
    }
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
    $rand = sprintf("%'.06d", rand(1,999999));
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
   * @return \Moocky\Aliyunsms\ResultData
   */
  public function verify(string $phone, string $rand, ?string $type = 'verification', ?int $expires = 900){
    if($sms = $this->info($phone,$type)){;
      // 已经被验证
      if($sms->verified){
        return new ResultData('SMS_VERIFIED','验证码已被验证');
      }
      if($sms->created_at->getTimestamp() + $expires < time() ){
        return new ResultData('SMS_EXPIRED', '验证码已过期');
      }
      $name = $this->config['code_name'] ?? 'rand';
      if($sms->template_param && array_key_exists($name,$sms->template_param) && $sms->template_param[$name] === $rand){
        $sms->update(['verified' => 1]);
        return new ResultData('OK', '验证通过');
      }
      return new ResultData('SMS_FAILED', '验证失败');
    }
    return new ResultData('SMS_EMPTY', '没有找到短信日志');
  }
}
