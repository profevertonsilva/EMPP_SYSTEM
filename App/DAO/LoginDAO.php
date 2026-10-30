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
                        log_type,
                        log_status,
                        log_confirmed
                        ) VALUES (
                        :log_email,
                        :log_password,
                        :log_type,
                        'A',
                        :log_confirmed
                        )";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_email', $obj->log_email);
            $stmt->bindValue(':log_password', $obj->log_password);
            $stmt->bindValue(':log_type', $obj->log_type);
            $stmt->bindValue(':log_confirmed', $obj->log_confirmed ?: 'N');
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
                            r.res_photo,
                            r.res_name
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

    // Any login (administrators may have no researcher row), with the name when there is one
    public function searchAnyByEmail($log_email){
        try{
            $sql = "SELECT l.*, r.res_name
                    FROM login l
                    LEFT JOIN researcher r ON r.fk_login_log_id = l.log_id
                    WHERE l.log_email = :log_email
                    LIMIT 1";
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

    // True when any login (researcher or administrator) already uses the email
    public function emailExists($log_email){
        try{
            $sql = "SELECT 1 FROM login WHERE log_email = :log_email LIMIT 1";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_email', $log_email);
            $stmt->execute();
            return (bool) $stmt->fetchColumn();
        }catch(\PDOException $e){
            echo "Erro ao selecionar os dados: " . $e->getMessage();
        }
    }

    // Email verification: stores the code's hash and its expiry, resetting the attempts
    public function setVerificationCode($log_id, $codeHash, $expires){
        try{
            $sql = "UPDATE login SET
                        log_token = :log_token,
                        log_code_expires = :log_code_expires,
                        log_code_attempts = 0
                    WHERE
                        log_id = :log_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_token', $codeHash);
            $stmt->bindValue(':log_code_expires', $expires);
            $stmt->bindValue(':log_id', $log_id);
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }

    public function addCodeAttempt($log_id){
        try{
            $sql = "UPDATE login SET log_code_attempts = log_code_attempts + 1 WHERE log_id = :log_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_id', $log_id);
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }

    public function confirmEmail($log_id){
        try{
            $sql = "UPDATE login SET
                        log_confirmed = 'Y',
                        log_token = '',
                        log_code_expires = NULL,
                        log_code_attempts = 0
                    WHERE
                        log_id = :log_id";
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bindValue(':log_id', $log_id);
            return $stmt->execute();
        }catch(\PDOException $e){
            echo "Erro ao atualizar os dados: " . $e->getMessage();
        }
    }




}
