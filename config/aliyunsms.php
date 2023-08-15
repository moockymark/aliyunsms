<?php
/**
 * Email: moocky@moocky.net
 * Created by Moocky
 * Date: 2023/8/14
 */
return [
	'access_key_id' => env('ALIYUN_ACCESS_KEY_ID'),
	'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
  'endpoint' => env('ALIYUN_DYSMS_ENDPOINT','dysmsapi.aliyuncs.com'),
  'template_code' => env('ALIYUN_DYSMS_VERIFICATION_TEMPLATE',''),
  'sign_name' => env('ALIYUN_DYSMS_SIGN_NAME',''),
  'sms_log_table' => env('ALIYUN_DYSMS_SMS_LOG_TABLE',''),
];