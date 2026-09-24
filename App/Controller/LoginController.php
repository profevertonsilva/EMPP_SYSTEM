<?php

namespace App\Controller;

use FW\Controller\FuncoesGlobais;

use FW\Controller\Action;
use App\DAO\CountryDAO;
use App\DAO\LoginDAO;
use App\Model\LoginModel;
use App\Model\ResearcherModel;
use App\DAO\ResearcherDAO;



class LoginController extends Action{

    // Load the View Login
    public function signin(){
        $title = "EMPP";
        $title_page = "EMPP - Electrospun Membrane Property Predictor";

        $this->getView()->title = $title;
        $this->getView()->title_page = $title_page;

        $this->render('sign_in', '');
    }

    // Load the View Forgot-password
    public function forgotPassword(){
        $title = "EMPP";
        $title_page = "EMPP - Electrospun Membrane Property Predictor";

        $this->getView()->title = $title;
        $this->getView()->title_page = $title_page;

        $this->render('forgot_password', '');
    }

    // Load the View Sign-UP
    public function signUp(){
        $title = "EMPP";
        $title_page = "EMPP - Electrospun Membrane Property Predictor";

        $countryDAO = new CountryDAO();
        $countries = $countryDAO->select();
        $this->getView()->countries = $countries;

        $this->getView()->title = $title;
        $this->getView()->title_page = $title_page;

        $this->render('sign_up', '');
    }

    public function actionSignUp(){
        /* var_dump($_POST);
        exit; */

        $global = new FuncoesGlobais();
        $name = $_POST['name'];
        $institution = $_POST['institution'];
        $purpose = $_POST['purpose'];
        $academic = $_POST['academic'];
        if($academic == "other"){
            $academic = $_POST['specify'];
        }
        $country = $_POST['country'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Validate the data
        if(!empty($name) && !empty($institution) && !empty($purpose) && !empty($academic) && !empty($country) && !empty($email) && !empty($password) ){
            
            $loginModel = new LoginModel();
            $loginModel->__set('log_email', $email);
            $loginModel->__set('log_password', sha1($password));
            $loginModel->__set('log_type', 'R');
            
            $loginDAO = new LoginDAO();
            $id = $loginDAO->create($loginModel);
            

            $researcherModel = new ResearcherModel();
            $researcherModel->__set('res_name', $name);
            $researcherModel->__set('res_institution', $institution);
            $researcherModel->__set('res_purpose', $purpose);
            $researcherModel->__set('res_academic', $academic);
            $researcherModel->__set('fk_country_cou_id', $country);
            $researcherModel->__set('fk_login_log_id', $id->log_id);

            $researcherDAO = new ResearcherDAO();
            $researcherDAO->create($researcherModel);

            header('Location: /sign-in');
        }

        // Save the data to the database
    }



    public function actionSignIn(){
        $email = $_POST['email-username'];
        $password = $_POST['password'];

        // Validate the data
        if(!empty($email) && !empty($password)){

            $loginDAO = new LoginDAO();
            $login = $loginDAO->searchByEmail($email);
            $passworddb = $login->log_password;
            
            
            if($passworddb === sha1($password)){
                $_SESSION['log_id'] = $login->log_id;
                $_SESSION['log_type'] = $login->log_type;
                $_SESSION['log_status'] = $login->log_status;
                $_SESSION['res_photo'] = $login->res_photo;
                if($_SESSION['log_type'] == 'A'){
                    header('Location: /dashboard/administrator');
                }else{
                    header('Location: /dashboard/researcher');
                }
            }

            
            
        }
    }
    public function actionSignOut(){
        session_destroy();
        header('Location: /sign-in');
    }
    
    
    

    public function validaAutenticacao() {

        
    }
}
