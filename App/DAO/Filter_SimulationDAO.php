<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\Filter_SimulationModel;

class Filter_SimulationDAO extends DAO
{
    public function create($obj)
    {
        try {
            $sql = "INSERT INTO 
                            filter_simulation (
                                                fis_material,
                                                fis_thickness,
                                                fis_diameter,
                                                fis_porosity,
                                                fis_temperature,
                                                fis_pressure,
                                                fis_velocity,
                                                fis_area,
                                                fis_density,
                                                fis_size_min,
                                                fis_size_max,
                                                fis_concentration,
                                                fis_class,
                                                fk_research_ree_id
                            ) VALUES (
                                                :material,
                                                :thickness,
                                                :diameter,
                                                :porosity,
                                                :temperature,
                                                :pressure,
                                                :velocity,
                                                :area,
                                                :density,
                                                :size_min,
                                                :size_max,
                                                :concentration,
                                                :fis_class,
                                                :fk_research_ree_id
                            )";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':material', $obj->__get('fis_material'));
            $stmt->bindValue(':thickness', $obj->__get('fis_thickness'));
            $stmt->bindValue(':diameter', $obj->__get('fis_diameter'));
            $stmt->bindValue(':porosity', $obj->__get('fis_porosity'));
            $stmt->bindValue(':temperature', $obj->__get('fis_temperature'));
            $stmt->bindValue(':pressure', $obj->__get('fis_pressure'));
            $stmt->bindValue(':velocity', $obj->__get('fis_velocity'));
            $stmt->bindValue(':area', $obj->__get('fis_area'));
            $stmt->bindValue(':density', $obj->__get('fis_density'));
            $stmt->bindValue(':size_min', $obj->__get('fis_size_min'));
            $stmt->bindValue(':size_max', $obj->__get('fis_size_max'));
            $stmt->bindValue(':concentration', $obj->__get('fis_concentration'));
            $stmt->bindValue(':fis_class', $obj->__get('fis_class'));
            $stmt->bindValue(':fk_research_ree_id', $obj->__get('fk_research_ree_id'));
            if ($stmt->execute()) {
                // If the insertion was successful, get the ID of the last inserted row
                return $this->getConn()->lastInsertId();
            } else {
                // If the insertion failed, return false
                return false;
            }
        } catch (\PDOException $e) {
            echo "Erro ao inserir os dados: " . $e->getMessage();
        }
    }
           

    public function delete($obj) {
        try {
            $sql = "DELETE FROM filter_simulation WHERE fsi_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj->__get('fsi_id'));
            return $stmt->execute();
        } catch (\PDOException $e) {
            echo "Erro ao deletar os dados: " . $e->getMessage();
        }
    }

    public function update($obj) {
        try {
            
            $sql = "UPDATE filter_simulation SET 
                        fis_material = :material,
                        fis_thickness = :thickness,
                        fis_diameter = :diameter,
                        fis_porosity = :porosity,
                        fis_temperature = :temperature,
                        fis_pressure = :pressure,
                        fis_velocity = :velocity,
                        fis_area = :area,
                        fis_density = :density,
                        fis_size_min = :size_min,
                        fis_size_max = :size_max,
                        fis_concentration = :concentration,
                        fk_research_ree_id = :fk_research_ree_id
                    WHERE fis_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':material', $obj->__get('fis_material'));
            $stmt->bindValue(':thickness', $obj->__get('fis_thickness'));
            $stmt->bindValue(':diameter', $obj->__get('fis_diameter'));
            $stmt->bindValue(':porosity', $obj->__get('fis_porosity'));
            $stmt->bindValue(':temperature', $obj->__get('fis_temperature'));
            $stmt->bindValue(':pressure', $obj->__get('fis_pressure'));
            $stmt->bindValue(':velocity', $obj->__get('fis_velocity'));
            $stmt->bindValue(':area', $obj->__get('fis_area'));
            $stmt->bindValue(':density', $obj->__get('fis_density'));
            $stmt->bindValue(':size_min', $obj->__get('fis_size_min'));
            $stmt->bindValue(':size_max', $obj->__get('fis_size_max'));
            $stmt->bindValue(':concentration', $obj->__get('fis_concentration'));
            $stmt->bindValue(':id', $obj->__get('fis_id'));
            $stmt->bindValue(':fk_research_ree_id', $obj->__get('fk_research_ree_id'));
        } catch (\PDOException $e) {
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }
    public function searchById($obj) {
        try {
            // Latest simulation of the study (a re-run replaces the previous one on screen)
            $sql = "SELECT * FROM filter_simulation WHERE fk_research_ree_id = :id ORDER BY fis_id DESC LIMIT 1";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $filterSimulationModel = new Filter_SimulationModel();
                $global = new FuncoesGlobais();
                $global->popularModel($filterSimulationModel, $result);
                return $filterSimulationModel;
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }
    public function select() {
        try {
            $sql = "SELECT * FROM filter_simulation";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $filterSimulations = [];
            foreach ($results as $result) {
                $filterSimulationModel = new Filter_SimulationModel();
                $global = new FuncoesGlobais();
                $global->popularModel($filterSimulationModel, $result);
                $filterSimulations[] = $filterSimulationModel;
            }
            return $filterSimulations;
        } catch (\PDOException $e) {
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }


    public function searchFilterSimulation($obj){
        try {
            $sql = "SELECT * FROM filter_simulation WHERE fis_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $filterSimulationModel = new Filter_SimulationModel();
                $global = new FuncoesGlobais();
                $global->popularModel($filterSimulationModel, $result);
                return $filterSimulationModel;
            } else {
                return null;
            }
        } catch (\PDOException $e) {
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }
}