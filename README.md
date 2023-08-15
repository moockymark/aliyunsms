# aliyunsms
基于laravel框架的 阿里云 SDK alibabacloud/dysmsapi-20180501接入短信服务

# 安装

composer require moocky/aliyunsms dev-master

# 基于laravel框架的使用方法

## 加载
在config/app的providers中添加 Moocky\Aliyunsms\LaravelServiceProvider::class

## 控制台运行

php artisan vendor:publish --provider=Moocky\Aliyunsms\LaravelServiceProvider

## 配置
根据新增的aliyunsms.php 文件，在.env文件中添加环境变量：

ALIYUN_ACCESS_KEY_ID=your access key
ALIYUN_ACCESS_KEY_SECRET=your access secret
ALIYUN_DYSMS_ENDPOINT=endpoint         # 短信发送节点，没有配置时为dysmsapi.aliyuncs.com
ALIYUN_DYSMS_SIGN_NAME=your sign name  # 短信签名，必须配置
ALIYUN_DYSMS_VERIFICATION_TEMPLATE=verification template code # 验证码模板代码，不发送验证可不配置
ALIYUN_DYSMS_SMS_LOG_TABLE=sms_log     # 短信日志表名称


## 使用
```PHP
use Moocky\Aliyunsms\Aliyunsms


# 发送普通短信
#
# $phone 手机号
# $templateCode 短信模板
# $templateParam 短信参数
# $type 短信类型，默认为normal
app('aliyunsms')->send('13042262923','SMS_112233445566',['rand' => 123456],'normal');
app('aliyunsms')->send('13042262923','SMS_556677889900');


# 发送验证码
#
# $phone 手机号
# $type 验证码类型，默认为verification
app('aliyunsms')->verification('13042262923','verification');
app('aliyunsms')->verification('13042262923');

# 校验验证码
#
# $phone 手机号
# $rand 验证码
# $type 验证码类型，默认为verification
# $expires 验证码有效时间，默认为600秒
app('aliyunsms')->verify('13042262923','123456','verification',1200);
app('aliyunsms')->verify('13042262923','123456');
```

# 非laravel框架的使用方法

```PHP
$config = [
	'access_key_id' => 'your access key',
	'access_key_secret' => 'your access secret',
	'sign_name' => 'your sign name',
	'endpoint' => 'endpoint',
	'template_code' => 'SMS_112233445566',
	'sms_log_table' => 'sms_log',
];

use Moocky\Aliyunsms\Aliyunsms


# 发送普通短信
#
# $phone 手机号
# $templateCode 短信模板
# $templateParam 短信参数
# $type 短信类型，默认为normal
$aliyunsms = new Aliyunsms($config);
$aliyunsms->send('13042262923','SMS_112233445566',['rand' => 123456],'normal');
$aliyunsms->send('13042262923','SMS_556677889900');

# 发送验证码
#
# $phone 手机号
# $type 验证码类型，默认为verification
$aliyunsms = new Aliyunsms($config);
$aliyunsms->verification('13042262923','verification');
$aliyunsms->verification('13042262923');

# 校验验证码
#
# $phone 手机号
# $rand 验证码
# $type 验证码类型，默认为verification
# $expires 验证码有效时间，默认为600秒
$aliyunsms = new Aliyunsms($config);
$aliyunsms->verify('13042262923','123456','verification',1200);
$aliyunsms->verify('13042262923','123456');