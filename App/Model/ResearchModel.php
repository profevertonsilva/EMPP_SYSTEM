<?php

namespace App\Model;

class ResearchModel
{
    private $ree_id;
    private $ree_name;
    private $ree_description;
    private $ree_file;
    private $ree_composition;
    private $ree_flow;
    private $ree_voltage;
    private $ree_distance;
    private $ree_rotation;
    private $ree_translation;
    private $ree_create;
    private $fk_login_log_id;

    private $res_name;
    // Vem do LEFT JOIN com research_results (resultado mais recente); null se nao processada.
    private $rre_porosity;
    

    

    public function __set($nome, $valor)
    {
        $this->$nome = $valor;
    }

    public function __get($nome)
    {
        return $this->$nome;
    }
}
