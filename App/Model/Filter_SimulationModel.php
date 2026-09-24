<?php

namespace App\Model;

class Filter_SimulationModel
{
    private $fis_id;
    private $fis_material;
    private $fis_thickness;
    private $fis_diameter;
    private $fis_porosity;
    private $fis_temperature;
    private $fis_pressure;
    private $fis_velocity;
    private $fis_area;
    private $fis_density;
    private $fis_size_min;
    private $fis_size_max;
    private $fis_concentration;
    private $fis_class;
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
