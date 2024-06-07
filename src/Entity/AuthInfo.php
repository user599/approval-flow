<?php

namespace Js3\ApprovalFlow\Entity;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;


class AuthInfo implements Arrayable, Jsonable, JsonSerializable
{


    const AUTH_TYPE_FRONT = 1;
    const AUTH_TYPE_ADMIN = 2;

    /**
     * @var 用户标识
     */
    private $auth_type;

    /**
     * @var 用户具体信息
     */
    private $auth_data;

    /**
     * @param  $auth_type
     * @param  $auth_data
     */
    public function __construct($auth_data, $auth_type)
    {
        $this->auth_type = $auth_type;
        $this->auth_data = $auth_data;
    }

    /**
     * @explain:基于id和类型创建身份信息
     * @param $auth_id
     * @param $auth_type
     * @return self
     * @author: wzm
     * @date: 2024/6/3 9:12
     * @remark: 用于创建身份模型，但不存在详细当前用户的信息
     */
    public static function createByAuthIdAndType($auth_id, $auth_type)
    {
        return new self(["id" => $auth_id], $auth_type);
    }

    /**
     * @return
     */
    public function getAuthType()
    {
        return $this->auth_type;
    }

    /**
     * @param  $auth_type
     * @return AuthInfo
     */
    public function setAuthType($auth_type): AuthInfo
    {
        $this->auth_type = $auth_type;
        return $this;
    }

    /**
     * @return
     */
    public function getAuthData()
    {
        return $this->auth_data;
    }

    /**
     * @param  $auth_data
     * @return AuthInfo
     */
    public function setAuthData($auth_data): AuthInfo
    {
        $this->auth_data = $auth_data;
        return $this;
    }

    /**
     * @explain:获取用户id
     * @return mixed|null
     * @author: wzm
     * @date: 2024/5/17 11:12
     * @remark:
     */
    public function getAuthId()
    {
        return $this->auth_data["id"] ?? null;
    }

    /**
     * @explain:获取用户凭证负载
     * @return array
     * @author: wzm
     * @date: 2024/5/17 11:13
     * @remark:
     */
    public function getAuthPayload()
    {
        return [
            "id" => $this->getAuthId(),
            "type" => $this->auth_type
        ];
    }

    /**
     * @explain: 获取用户唯一标识
     * @return string
     * @author: wzm
     * @date: 2024/6/3 10:28
     * @remark: 将id，用户类型使用-拼接
     */
    public function getAuthKey()
    {
        return implode("-", [$this->getAuthId(), $this->getAuthType()]);
    }

    /**
     * @explain: 是否同一用户
     * @param $auth_id
     * @param $auth_type
     * @return bool
     * @author: wzm
     * @date: 2024/5/23 17:21
     * @remark:
     */
    public function isSameMember($auth_id, $auth_type): bool
    {
        return $auth_id == $this->getAuthId() && $auth_type == $this->getAuthType();
    }

    public function toArray()
    {
        return [
            "auth_data" => $this->auth_data,
            "auth_type" => $this->auth_type
        ];
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
