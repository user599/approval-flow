<?php

namespace Js3\ApprovalFlow\Entity;

class AuthInfo
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
    public function __construct( $auth_data,$auth_type)
    {
        $this->auth_type = $auth_type;
        $this->auth_data = $auth_data;
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
    public function setAuthData( $auth_data): AuthInfo
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
        return $this->auth_data["id"]??null;
    }

    /**
     * @explain:获取用户凭证负载
     * @return array
     * @author: wzm
     * @date: 2024/5/17 11:13
     * @remark:
     */
    public function getAuthPayload() {
        return [
            "id" => $this->getAuthId(),
            "type" => $this->auth_type
        ];
    }



}
