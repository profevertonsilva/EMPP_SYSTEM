<?php

namespace App\Model;

class CountryModel
{
    private $cou_id;
    private $cou_iso;
    private $cou_name;
    private $cou_nicename;
    private $cou_iso3;
    private $cou_numcode;
    private $cou_phonecode;
    

    

    public function __set($nome, $valor)
    {
        $this->$nome = $valor;
    }

    public function __get($nome)
    {
        return $this->$nome;
    }
}
