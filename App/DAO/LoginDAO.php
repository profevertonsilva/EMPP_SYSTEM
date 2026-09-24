<?php

namespace App\DAO;

use App\DAO;
use FW\Controller\FuncoesGlobais;


use App\Model\LoginModel;


class LoginDAO extends DAO{

    
    public function login($obj){}
    public function create($obj){
        try{
            $sql = "INSERT INTO login (
                        log_email,
                        log_password,
                        log_type
                        ) VALUES (
                        :log_email,
                        :log_password,
                        :log_type
                        )";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_email', $obj->log_email);
            $stmt->bindValue(':log_password', $obj->log_password);
            $stmt->bindValue(':log_type', $obj->log_type);
            $stmt->execute();
            $obj->log_id = $this->getConn()->lastInsertId();
            return $obj;
        }catch(\PDOException $e){
            echo "Erro ao inserir os dados: " . $e->getMessage();
        }
    }
    public function delete($obj){}
    public function update($obj){}
    public function searchById($obj){
        try{
            $sql = "SELECT * FROM login WHERE log_id = :log_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_id', $obj);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($result){
                $loginModel = new LoginModel();
                $global = new FuncoesGlobais();
                $global->popularModel($loginModel, $result );
                return $loginModel;
            }else{
                return null;
            }
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }
    public function select(){}

    public function searchByEmail($log_email){
        try{
            
            $sql = "SELECT 
                            l.*,
                            r.res_photo
                        FROM 
                            login l,
                            researcher r
                        WHERE 
                            l.log_email = :log_email AND
                            l.log_id = r.fk_login_log_id
                    ";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_email', $log_email);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if($result){
                $loginModel = new LoginModel();
                $global = new FuncoesGlobais();
                $global->popularModel($loginModel, $result );
                return $loginModel;
            }else{
                return null;
            }
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }

    public function updatePassword($obj){
        try{
            $sql = "UPDATE login SET 
                        log_password = :log_password
                    WHERE 
                        log_id = :log_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_password', $obj->log_password);
            $stmt->bindValue(':log_id', $obj->log_id);
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }




}
