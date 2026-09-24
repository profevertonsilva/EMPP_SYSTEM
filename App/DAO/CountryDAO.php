<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\CountryModel;


class CountryDAO extends DAO{

    
    public function create($obj){}
    public function delete($obj){}
    public function update($obj){}
    public function searchById($obj){
        try{
            $sql = "SELECT * FROM country WHERE cou_id = :cou_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':cou_id', $obj);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($result){
                $countryModel = new CountryModel();
                $global = new FuncoesGlobais();
                $global->popularModel($countryModel, $result );
                return $countryModel;
            }else{
                return null;
            }
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }
    public function select(){
        try{
            $country = array();
            $sql = "SELECT * FROM country";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_CLASS);

            foreach($result as $row){
                $countryModel = new CountryModel();
                $global = new FuncoesGlobais();
                $global->popularModel($countryModel, $row );

                array_push($country, $countryModel);
            }
            return $country;
        
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }   
    }




}
