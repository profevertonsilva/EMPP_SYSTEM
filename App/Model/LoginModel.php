<?php

namespace App\Model;

class LoginModel
{
    private $log_id;
    private $log_email;
    private $log_password;
    private $log_status;
    private $log_confirmed;
    private $log_type;
    private $log_token;
    private $log_code_expires;
    private $log_code_attempts;
    private $log_create;


    private $res_photo;
    private $res_name;
    

    

    public function __set($nome, $valor)
    {
        $this->$nome = $valor;
    }

    public function __get($nome)
    {
        return $this->$nome;
    }
}
