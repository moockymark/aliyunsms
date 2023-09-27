<?php
namespace Moocky\Aliyunsms\Models;

use Route,DB,Str,Auth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SmsLog extends Model
{
  const UPDATED_AT = null;
  /**
   * 与模型关联的数据表。
   *
   * @var string
   */
  protected $table = 'sms_log';
  /**
  * 这个属性应该被转换为原生类型.
  *
  * @var array
  */
  protected $casts = [
    'template_param'   => 'array'
  ];
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['id','phone','sign_name','template_code','template_param','type','verified','code','biz_id','request_id'];
}