<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\CountryModel;
use App\Model\ResearcherModel;


class ResearcherDAO extends DAO{

    public function create($obj){
        try{
            $sql = "INSERT INTO researcher (
                        res_name,
                        res_institution,
                        res_purpose,
                        res_academic,
                        fk_country_cou_id,
                        fk_login_log_id
                        ) VALUES (
                        :res_name,
                        :res_institution,
                        :res_purpose,
                        :res_academic,
                        :fk_country_cou_id,
                        :fk_login_log_id
                        )";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':res_name', $obj->res_name);
            $stmt->bindValue(':res_institution', $obj->res_institution);
            $stmt->bindValue(':res_purpose', $obj->res_purpose);
            $stmt->bindValue(':res_academic', $obj->res_academic);
            $stmt->bindValue(':fk_country_cou_id', $obj->fk_country_cou_id);
            $stmt->bindValue(':fk_login_log_id', $obj->fk_login_log_id);
            $stmt->execute();
            $obj->res_id = $this->getConn()->lastInsertId();
            return $obj;
        }catch(\PDOException $e){
            echo "Erro ao inserir os dados: " . $e->getMessage();
        }
    }
    public function delete($obj){}
    public function update($obj){
        try{
            $sql = "UPDATE researcher SET 
                        res_name = :res_name,
                        res_institution = :res_institution,
                        res_purpose = :res_purpose,
                        res_academic = :res_academic,
                        fk_country_cou_id = :fk_country_cou_id
                    WHERE 
                        res_id = :res_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':res_name', $obj->res_name);
            $stmt->bindValue(':res_institution', $obj->res_institution);
            $stmt->bindValue(':res_purpose', $obj->res_purpose);
            $stmt->bindValue(':res_academic', $obj->res_academic);
            $stmt->bindValue(':fk_country_cou_id', $obj->fk_country_cou_id);
            $stmt->bindValue(':res_id', $obj->res_id);
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }
    public function searchById($obj){    }
    public function select(){
        try{
            $researcher = array();
            $sql = "SELECT
                            r.*,
                            l.log_type,
                            l.log_email,
                            c.cou_id,
                            c.cou_nicename,
                            (SELECT COUNT(*) FROM research x WHERE x.fk_login_log_id = r.fk_login_log_id) AS research_count
                        FROM
                            researcher r,
                            country c,
                            login l
                        WHERE
                            r.fk_country_cou_id = c.cou_id AND
                            r.fk_login_log_id = l.log_id
                        ORDER BY
                            r.res_name
                    ";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $row){
                $researcherModel = new ResearcherModel();
                $global = new FuncoesGlobais();
                $global->popularModel($researcherModel, $row);
                array_push($researcher, $researcherModel);
            }
            return $researcher;
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }

    public function searchByLogId($log_id){
        try{
            
            $sql = "SELECT
                            r.*,
                            l.log_type,
                            l.log_email,
                            l.log_create,
                            c.cou_id,
                            c.cou_nicename
                        FROM
                            researcher r,
                            country c,
                            login l
                        WHERE
                            r.fk_login_log_id = :fk_login_log_id AND
                            r.fk_country_cou_id = c.cou_id AND
                            r.fk_login_log_id = l.log_id
                    ";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':fk_login_log_id', $log_id);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($result){
                $researcherModel = new ResearcherModel();
                $global = new FuncoesGlobais();
                $global->popularModel($researcherModel, $result);
                return $researcherModel;
            }else{
                return null;
            }
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }


    // $log_id is the login id (the caller passes $_SESSION['log_id'])
    public function updatePhoto($log_id, $photo){
        try{
            $sql = "UPDATE researcher SET
                        res_photo = :res_photo
                    WHERE
                        fk_login_log_id = :log_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':res_photo', $photo);
            $stmt->bindValue(':log_id', $log_id);
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }

    public function countResearchers(){
        $sql = "SELECT 
                        COUNT(*) AS researchers 
                    FROM 
                        researcher 
                ";
        $stmt = $this->getConn()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['researchers'];

    }
}