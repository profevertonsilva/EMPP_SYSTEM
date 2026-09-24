<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\ResearchModel;


class ResearchDAO extends DAO{

    
    public function create($obj){
        try{

            $ree_name = $obj->__get('ree_name');
            $ree_description = $obj->__get('ree_description');
            $ree_file = $obj->__get('ree_file');
            $ree_flow = $obj->__get('ree_flow');
            $ree_voltage = $obj->__get('ree_voltage');
            $ree_distance = $obj->__get('ree_distance');
            $fk_login_log_id = $obj->__get('fk_login_log_id');



            $sql = "
                    INSERT INTO research(
                            ree_name,
                            ree_description,
                            ree_file,
                            ree_flow,
                            ree_voltage,
                            ree_distance,
                            fk_login_log_id
                        )VALUES(
                            :ree_name,
                            :ree_description,
                            :ree_file,
                            :ree_flow,
                            :ree_voltage,
                            :ree_distance,
                            :fk_login_log_id
                        )
            ";

            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindParam(':ree_name', $ree_name);
            $stmt->bindParam(':ree_description', $ree_description);
            $stmt->bindParam(':ree_file', $ree_file);
            $stmt->bindParam(':ree_flow', $ree_flow);
            $stmt->bindParam(':ree_voltage', $ree_voltage);
            $stmt->bindParam(':ree_distance', $ree_distance);
            $stmt->bindParam(':fk_login_log_id', $fk_login_log_id);

            if($stmt->execute()){
                return $this->getConn()->lastInsertId();
            }else{
                return 0;
            }


        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }  



    }
    public function delete($obj){}
    public function update($obj){}
    public function searchById($ree_id) {
        try {
            $sql = "SELECT 
                    r.*,
                    re.res_name 
                FROM 
                    research r,
                    researcher re
                WHERE
                    r.ree_id = :ree_id AND
                    r.fk_login_log_id = re.fk_login_log_id
            ";
    
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindParam(':ree_id', $ree_id);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null; // Ou lançar uma exceção específica
            }
    
            $researchModel = new ResearchModel();
            $global = new FuncoesGlobais();
            $global->popularModel($researchModel, $result);
    
            return $researchModel;
    
        } catch(\PDOException $e) {
            // Log do erro seria melhor que apenas echo
            error_log("Erro ao selecionar pesquisa por ID: " . $e->getMessage());
            throw new \Exception("Erro ao buscar pesquisa");
        }
    }
    public function select(){
        try{
            $research = array();
            // Latest result per study, same criterion as searchMyResearch
            $sql = "SELECT
                            r.*,
                            re.res_name,
                            rr.rre_porosity
                        FROM research r
                        INNER JOIN login l
                            ON l.log_id = r.fk_login_log_id
                        INNER JOIN researcher re
                            ON re.fk_login_log_id = l.log_id
                        LEFT JOIN research_results rr
                            ON rr.rre_id = (
                                SELECT MAX(rr2.rre_id)
                                FROM research_results rr2
                                WHERE rr2.fk_research_ree_id = r.ree_id
                            )
                        ORDER BY
                            r.ree_create DESC
                    ";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_CLASS);

            foreach($result as $row){
                $researchModel = new ResearchModel();
                $global = new FuncoesGlobais();
                $global->popularModel($researchModel, $row);

                array_push($research, $researchModel);
            }
            return $research;
        
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }   
    }

    public function countResearch(){
        $sql = "SELECT 
                        COUNT(*) AS researchs 
                    FROM 
                        research 
                ";
        $stmt = $this->getConn()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['researchs'];

    }


    public function searchMyResearch($obj){
        try{
            $research = array();
            // O LEFT JOIN traz a porosidade do resultado mais recente de cada pesquisa
            // (mesmo criterio do searchById do Research_ResultsDAO); fica NULL quando
            // a pesquisa ainda nao foi processada.
            $sql = "SELECT
                            r.*,
                            re.res_name,
                            rr.rre_porosity
                        FROM research r
                        INNER JOIN login l
                            ON l.log_id = r.fk_login_log_id
                        INNER JOIN researcher re
                            ON re.fk_login_log_id = l.log_id
                        LEFT JOIN research_results rr
                            ON rr.rre_id = (
                                SELECT MAX(rr2.rre_id)
                                FROM research_results rr2
                                WHERE rr2.fk_research_ree_id = r.ree_id
                            )
                        WHERE
                            r.fk_login_log_id = :log_id
                        ORDER BY
                            r.ree_create DESC
                    ";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindParam(':log_id', $obj);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_CLASS);

            foreach($result as $row){
                $researchModel = new ResearchModel();
                $global = new FuncoesGlobais();
                $global->popularModel($researchModel, $row);

                array_push($research, $researchModel);
            }
            return $research;
        
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }

    /**
     * Rows for the image dataset export: every study with its process
     * parameters and latest Haralick/porosity result (NULL when not processed).
     * Returns plain associative arrays; optionally limited to one researcher.
     */
    public function selectDataset($log_id = null){
        $sql = "SELECT
                        r.ree_id,
                        r.ree_name,
                        r.ree_file,
                        r.ree_flow,
                        r.ree_voltage,
                        r.ree_distance,
                        r.ree_create,
                        r.fk_login_log_id,
                        re.res_name,
                        re.res_institution,
                        rr.rre_dissimilarity,
                        rr.rre_correlation,
                        rr.rre_energy,
                        rr.rre_homogeneity,
                        rr.rre_porosity
                    FROM research r
                    INNER JOIN researcher re
                        ON re.fk_login_log_id = r.fk_login_log_id
                    LEFT JOIN research_results rr
                        ON rr.rre_id = (
                            SELECT MAX(rr2.rre_id)
                            FROM research_results rr2
                            WHERE rr2.fk_research_ree_id = r.ree_id
                        )
                    " . ($log_id !== null ? "WHERE r.fk_login_log_id = :log_id" : "") . "
                    ORDER BY r.ree_id
                ";
        $stmt = $this->getConn()->prepare($sql);
        if($log_id !== null){
            $stmt->bindValue(':log_id', $log_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}
