<?php


namespace Js3\ApprovalFlow\Encrypter;

interface Encrypter
{


    public function encrypt($payload): string;

    public function decrypt($token);
}
