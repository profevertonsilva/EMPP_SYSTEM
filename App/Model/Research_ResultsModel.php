<?php

namespace App\Model;

class Research_ResultsModel
{
    private $rre_id;
    private $rre_dissimilarity;
    private $rre_correlation;
    private $rre_energy;
    private $rre_homogeneity;
    private $rre_porosity;
    private $fk_research_ree_id;
    

    

    public function __set($nome, $valor)
    {
        $this->$nome = $valor;
    }

    public function __get($nome)
    {
        return $this->$nome;
    }
}
