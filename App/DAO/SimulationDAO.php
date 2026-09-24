<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\SimulationModel;

class SimulationDAO extends DAO
{
    public function create($obj)
    {
        try {
            $sql = "INSERT INTO 
                            simulation (
                                                sim_density,
                                                sim_permeability_k1,
                                                sim_permeability_k2,
                                                sim_gravitational,
                                                sim_air_density,
                                                sim_air_viscosity,
                                                sim_air_free_path,
                                                sim_test_pressure,
                                                sim_boltzmann,
                                                sim_kuwabara,
                                                sim_knudsen,
                                                fk_filter_simulation_fis_id
                            ) 
                    VALUES (
                                :density,
                                :permeability_k1,
                                :permeability_k2,
                                :gravitational,
                                :air_density,
                                :air_viscosity,
                                :air_free_path,
                                :test_pressure,
                                :boltzmann,
                                :kuwabara,
                                :knudsen,
                                :fk_filter_simulation_fis_id
                    )";
                                
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':density', $obj->__get('sim_density'));
            $stmt->bindValue(':permeability_k1', $obj->__get('sim_permeability_k1'));
            $stmt->bindValue(':permeability_k2', $obj->__get('sim_permeability_k2'));
            $stmt->bindValue(':gravitational', $obj->__get('sim_gravitational'));
            $stmt->bindValue(':air_density', $obj->__get('sim_air_density'));
            $stmt->bindValue(':air_viscosity', $obj->__get('sim_air_viscosity'));
            $stmt->bindValue(':air_free_path', $obj->__get('sim_air_free_path'));
            $stmt->bindValue(':test_pressure', $obj->__get('sim_test_pressure'));
            $stmt->bindValue(':boltzmann', $obj->__get('sim_boltzmann'));
            $stmt->bindValue(':kuwabara', $obj->__get('sim_kuwabara'));
            $stmt->bindValue(':knudsen', $obj->__get('sim_knudsen'));          
            $stmt->bindValue(':fk_filter_simulation_fis_id', $obj->__get('fk_filter_simulation_fis_id'));
            return $stmt->execute();
            
        } catch (\PDOException $e) {
            echo "Erro ao inserir os dados: " . $e->getMessage();
        }
    }


    public function delete($obj) {
        try {
            $sql = "DELETE FROM simulation WHERE sim_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj->__get('sim_id'));
            return $stmt->execute();
        } catch (\PDOException $e) {
            echo "Erro ao deletar os dados: " . $e->getMessage();
        }
    }

    public function update($obj) {
        try {
            $sql = "UPDATE simulation SET 
                        sim_density = :density,
                        sim_permeability_k1 = :permeability_k1,
                        sim_permeability_k2 = :permeability_k2,
                        sim_gravitational = :gravitational,
                        sim_air_density = :air_density,
                        sim_air_viscosity = :air_viscosity,
                        sim_air_free_path = :air_free_path,
                        sim_test_pressure = :test_pressure,
                        sim_boltzmann = :boltzmann,
                        sim_kuwabara = :kuwabara,
                        sim_knudsen = :knudsen,
                        sim_penetration = :penetration,
                        sim_mpps = :mpps,
                        sim_filter_efficiency = :filter_efficiency,
                        sim_flow_rate = :flow_rate,
                        sim_pressure_drop = :pressure_drop,
                        sim_viscous_contribution = :viscous_contribution,
                        sim_inertial_contribution = :inertial_contribution,
                        sim_quality_factor = :quality_factor,
                        sim_concentration = :concentration,
                        sim_dm = :dm,
                        sim_a = :a,
                        sim_sum_discrete = :sum_discrete,
                        sim_sum_norm = :sum_norm,
                        sim_sum_dpi = :sum_dpi,
                        sim_dsauter = :dsauter,
                        sim_d50 = :d50,
                        sim_d90 = :d90,
                        sim_d10 = :d10,
                        sim_dpi = :dpi
                    WHERE sim_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':density', $obj->__get('sim_density'));
            $stmt->bindValue(':permeability_k1', $obj->__get('sim_permeability_k1'));
            $stmt->bindValue(':permeability_k2', $obj->__get('sim_permeability_k2'));
            $stmt->bindValue(':gravitational', $obj->__get('sim_gravitational'));
            $stmt->bindValue(':air_density', $obj->__get('sim_air_density'));
            $stmt->bindValue(':air_viscosity', $obj->__get('sim_air_viscosity'));
            $stmt->bindValue(':air_free_path', $obj->__get('sim_air_free_path'));
            $stmt->bindValue(':test_pressure', $obj->__get('sim_test_pressure'));
            $stmt->bindValue(':boltzmann', $obj->__get('sim_boltzmann'));
            $stmt->bindValue(':kuwabara', $obj->__get('sim_kuwabara'));
            $stmt->bindValue(':knudsen', $obj->__get('sim_knudsen'));
            $stmt->bindValue(':penetration', $obj->__get('sim_penetration'));
            $stmt->bindValue(':mpps', $obj->__get('sim_mpps'));
            $stmt->bindValue(':filter_efficiency', $obj->__get('sim_filter_efficiency'));
            $stmt->bindValue(':flow_rate', $obj->__get('sim_flow_rate'));
            $stmt->bindValue(':pressure_drop', $obj->__get('sim_pressure_drop'));
            $stmt->bindValue(':viscous_contribution', $obj->__get('sim_viscous_contribution'));
            $stmt->bindValue(':inertial_contribution', $obj->__get('sim_inertial_contribution'));
            $stmt->bindValue(':quality_factor', $obj->__get('sim_quality_factor'));
            $stmt->bindValue(':concentration', $obj->__get('sim_concentration'));
            $stmt->bindValue(':dm', $obj->__get('sim_dm'));
            $stmt->bindValue(':a', $obj->__get('sim_a'));
            $stmt->bindValue(':sum_discrete', $obj->__get('sim_sum_discrete'));
            $stmt->bindValue(':sum_norm', $obj->__get('sim_sum_norm'));
            $stmt->bindValue(':sum_dpi', $obj->__get('sim_sum_dpi'));
            $stmt->bindValue(':dsauter', $obj->__get('sim_dsauter'));
            $stmt->bindValue(':d50', $obj->__get('sim_d50'));
            $stmt->bindValue(':d90', $obj->__get('sim_d90'));
            $stmt->bindValue(':d10', $obj->__get('sim_d10'));
            $stmt->bindValue(':dpi', $obj->__get('sim_dpi'));
            $stmt->bindValue(':id', $obj->__get('sim_id'));
        }
        catch (\PDOException $e) {
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }

    public function searchById($obj) {
        try {
            $sql = "SELECT * FROM simulation WHERE sim_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj->__get('sim_id'));
            $stmt->execute();
            return $stmt->fetchObject(SimulationModel::class);
        } catch (\PDOException $e) {
            echo "Erro ao buscar os dados: " . $e->getMessage();
        }
    }


    public function select(){
        try {
            $sql = "SELECT * FROM simulation";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_CLASS, SimulationModel::class);
        } catch (\PDOException $e) {
            echo "Erro ao buscar os dados: " . $e->getMessage();
        }
    }


    public function searchSimulationByFilterId($id){
        try {
            $sql = "SELECT * FROM simulation WHERE fk_filter_simulation_fis_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $simulationModel = new SimulationModel();
                $global = new FuncoesGlobais();
                $global->popularModel($simulationModel, $result);
                return $simulationModel;
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }

}
