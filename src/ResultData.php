<?php
namespace Moocky\Aliyunsms;

use ArrayAccess;
use JsonSerializable;
use Str;

use Illuminate\Database\Eloquent\JsonEncodingException;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponseBody;

class ResultData implements ArrayAccess,JsonSerializable{
  /**
   * @var string
   */
  private $code = null;
  /**
   * @var string
   */
  private $message = null;
  /**
   * @var string
   */
  private $bizId = null;
  /**
   * @var string
   */
  private $requestId = null;

	public function __construct(string $code, string $message, ?string $bizId = null, ?string $requestId = null)
	{
		$this->code = $code;
		$this->message = $message;
		$this->bizId = $bizId;
		$this->requestId = $requestId;
	}
  /**
   * Dynamically retrieve attributes on the model.
   *
   * @param  \AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponseBody  $resp
   * @return static
   */
  public static function resp(SendSmsResponseBody $resp){
    return new static($resp->code,$resp->message,$resp->bizId,$resp->requestId);
  }
  /**
   * Dynamically retrieve attributes on the model.
   *
   * @param  string  $key
   * @return mixed
   */
  public function __get($key)
  {
    if(property_exists($this,$key)){
      return $this->{$key};
    }elseif(property_exists($this,Str::camel($key))){
      return $this->{Str::camel($key)};
    }
    return null;
  }
  /**
   * Dynamically set attributes on the model.
   *
   * @param  string  $key
   * @param  mixed  $value
   * @return void
   */
  public function __set($key, ?string $val = null)
  {
    if(property_exists($this,$key)){
      if($key == 'code' && strcasecmp($val,'OK') === 0){
        $this->{$key} = 'OK';
      }else{
        $this->{$key} = $val;
      }
    }elseif(property_exists($this,Str::camel($key))){
      $this->{Str::camel($key)} = $val;
    }
  }
  /**
   * Determine if the given attribute exists.
   *
   * @param  mixed  $offset
   * @return bool
   */
  public function offsetExists(mixed $offset): bool
  {
    return property_exists($this,$offset) || property_exists($this,Str::camel($offset));
  }

  /**
   * Get the value for a given offset.
   *
   * @param  mixed  $offset
   * @return mixed
   */
  public function offsetGet(mixed $offset): mixed
  {
    if(property_exists($this,$offset)){
      return $this->{$offset};
    }elseif(property_exists($this,Str::camel($offset))){
      return $this->{Str::camel($offset)};
    }
    return null;
  }

  /**
   * Set the value for a given offset.
   *
   * @param  mixed  $offset
   * @param  mixed  $value
   * @return void
   */
  public function offsetSet(mixed $offset, mixed $value): void
  {
    if(property_exists($this,$offset)){
      if($key == 'code' && strcasecmp($val,'OK') === 0){
        $this->{$offset} = 'OK';
      }else{
        $this->{$offset} = $value;
      }
    }elseif(property_exists($this,Str::camel($offset))){
      $this->{Str::camel($offset)} = $value;
    }
  }

  /**
   * Unset the value for a given offset.
   *
   * @param  mixed  $offset
   * @return void
   */
  public function offsetUnset(mixed $offset): void
  {
    if(property_exists($this,$offset)){
      $this->{$offset} = null;
    }elseif(property_exists($this,Str::camel($offset))){
      $this->{Str::camel($offset)} = null;
    }
  }

  /**
   * Determine if an attribute or relation exists on the model.
   *
   * @param  mixed  $key
   * @return bool
   */
  public function __isset(mixed $key)
  {
    return $this->offsetExists($key);
  }

  /**
   * Unset an attribute on the model.
   *
   * @param  mixed  $key
   * @return void
   */
  public function __unset(mixed $key)
  {
    $this->offsetUnset($key);
  }
  /**
   * Convert the model instance to an array.
   *
   * @return array
   */
  public function toArray()
  {
    $array = [];
    foreach(get_object_vars($this) as $key => $val){
      if($val){
        $array[Str::snake($key)] = $val;
      }
    }
    return $array;
  }
  /**
   * Convert the object into something JSON serializable.
   *
   * @return mixed
   */
  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }
  /**
   * Convert the model instance to JSON.
   *
   * @param  int  $options
   * @return string
   *
   * @throws \Illuminate\Database\Eloquent\JsonEncodingException
   */
  public function toJson($options = 0)
  {
    $json = json_encode($this->jsonSerialize(), $options);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw JsonEncodingException::forModel($this, json_last_error_msg());
    }
    return $json;
  }
  /**
   * Convert the model to its string representation.
   *
   * @return string
   */
  public function __toString()
  {
    return 'ResultData:'.$this->toJson();
  }
}
