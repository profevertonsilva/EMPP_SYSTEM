<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\Research_ResultsModel;


class Research_ResultsDAO extends DAO{

    
    public function create($obj){
        try{
            $sql = "INSERT INTO research_results (rre_dissimilarity, rre_correlation, rre_energy, rre_homogeneity, fk_research_ree_id) 
                    VALUES (:dissimilarity, :correlation, :energy, :homogeneity, :fk_research_ree_id)";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':dissimilarity', $obj->__get('rre_dissimilarity'));
            $stmt->bindValue(':correlation', $obj->__get('rre_correlation'));
            $stmt->bindValue(':energy', $obj->__get('rre_energy'));
            $stmt->bindValue(':homogeneity', $obj->__get('rre_homogeneity'));
            $stmt->bindValue(':fk_research_ree_id', $obj->__get('fk_research_ree_id'));
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao inserir os dados: " . $e->getMessage();
        }
    }
    public function delete($obj){
        try{
            $sql = "DELETE FROM research_results WHERE rre_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj->__get('rre_id'));
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao deletar os dados: " . $e->getMessage();
        }
    }
    public function update($obj){
        try{
            $sql = "UPDATE research_results SET rre_porosity = :porosity WHERE rre_id = :rre_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':porosity', $obj->__get('rre_porosity'));
            $stmt->bindValue(':rre_id', $obj->__get('rre_id'));
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }
    public function searchById($obj){
        try{
            $sql = "SELECT * FROM research_results WHERE fk_research_ree_id = :id ORDER BY rre_id DESC LIMIT 1";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':id', $obj);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($result){
                $researchResultsModel = new Research_ResultsModel();
                $global = new FuncoesGlobais();
                $global->popularModel($researchResultsModel, $result);
                return $researchResultsModel;
            }else{
                return null;
            }
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }
    public function select(){
        try{
            $sql = "SELECT * FROM research_results";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_CLASS, Research_ResultsModel::class);
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }   
    }

    public function updatePorosity($id, $porosity){
        try{
            $sql = "UPDATE research_results SET rre_porosity = :porosity WHERE fk_research_ree_id = :id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':porosity', $porosity);
            $stmt->bindValue(':id', $id);
            if($stmt->execute()){
                return true;
            }else{
                return false;
            }
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }



}
