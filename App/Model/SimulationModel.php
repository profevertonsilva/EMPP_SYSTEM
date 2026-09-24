<?php

namespace App\Model;

class SimulationModel
{
    private $sim_id;
    private $sim_density;
    private $sim_permeability_k1;
    private $sim_permeability_k2;
    private $sim_gravitational;
    private $sim_air_density;
    private $sim_air_viscosity;
    private $sim_air_free_path;
    private $sim_test_pressure;
    private $sim_boltzmann;
    private $sim_kuwabara;
    private $sim_knudsen;
    private $sim_penetration;
    private $sim_mpps;
    private $sim_filter_efficiency;
    private $sim_flow_rate;
    private $sim_pressure_drop;
    private $sim_viscous_contribution;
    private $sim_inertial_contribution;
    private $sim_quality_factor;
    private $sim_concentration;
    private $sim_dm;
    private $sim_a;
    private $sim_sum_discrete;
    private $sim_sum_norm;
    private $sim_sum_dpi;
    private $sim_dsauter;
    private $sim_d50;
    private $sim_d90;
    private $sim_d10;
    private $sim_dpi;
    private $fk_filter_simulation_fis_id;
    

    public function __set($nome, $valor)
    {
        $this->$nome = $valor;
    }

    public function __get($nome)
    {
        return $this->$nome;
    }
}
