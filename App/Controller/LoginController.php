<?php

namespace App\Controller;

use FW\Controller\FuncoesGlobais;

use FW\Controller\Action;
use App\DAO\CountryDAO;
use App\DAO\LoginDAO;
use App\Model\LoginModel;
use App\Model\ResearcherModel;
use App\DAO\ResearcherDAO;
use App\Mail\Mailer;



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
        $global = new FuncoesGlobais();
        $name = trim($_POST['name'] ?? '');
        $institution = trim($_POST['institution'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $academic = $_POST['academic'] ?? '';
        if($academic == "other"){
            $academic = trim($_POST['specific'] ?? '');
        }
        $country = $_POST['country'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Kept so the form comes back filled when it has to be corrected
        $_SESSION['signup_old'] = array_diff_key($_POST, ['password' => 1, 'password_confirm' => 1]);

        if(empty($name) || empty($institution) || empty($purpose) || empty($academic) || empty($country) || empty($email) || empty($password)){
            header('Location: /sign-up?error=fields');
            exit;
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            header('Location: /sign-up?error=email-invalid');
            exit;
        }
        if($password !== $passwordConfirm){
            header('Location: /sign-up?error=password-match');
            exit;
        }

        $loginDAO = new LoginDAO();
        if($loginDAO->emailExists($email)){
            header('Location: /sign-up?error=email-taken');
            exit;
        }

        $loginModel = new LoginModel();
        $loginModel->__set('log_email', $email);
        $loginModel->__set('log_password', $global->hashSenha($password));
        $loginModel->__set('log_type', 'R');
        $loginModel->__set('log_confirmed', 'N');
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

        unset($_SESSION['signup_old']);
        $this->startVerification($id->log_id, $email, $name);
        exit;
    }

    // Email verification ----------------------------------------------------

    const CODE_MINUTES = 15;
    const CODE_MAX_ATTEMPTS = 5;
    const RESEND_SECONDS = 60;

    // Emails a fresh code, remembers who is verifying and opens the code page
    private function startVerification($log_id, $email, $name){
        $_SESSION['verify'] = [
            'log_id' => $log_id,
            'email' => $email,
            'name' => $name,
            'sent_at' => 0,
        ];
        $sent = $this->sendCode('verify');
        header('Location: /verify-email' . ($sent ? '' : '?error=send'));
    }

    // $flow is the session key and the email's purpose: 'verify' or 'reset'
    private function sendCode($flow){
        $state = $_SESSION[$flow];
        $_SESSION[$flow]['sent_at'] = time();

        // Password reset for an email with no account: behave as if it was sent,
        // so the form does not reveal which emails are registered
        if(empty($state['log_id'])){
            return true;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + self::CODE_MINUTES * 60);

        $loginDAO = new LoginDAO();
        $loginDAO->setVerificationCode($state['log_id'], password_hash($code, PASSWORD_DEFAULT), $expires);

        $mailer = new Mailer();
        return $mailer->sendCode($flow, $state['email'], $state['name'], $code, self::CODE_MINUTES);
    }

    // Checks a typed code against the stored one; returns an error key or null
    private function checkCode($login, $code){
        if(!$login || (int) $login->log_code_attempts >= self::CODE_MAX_ATTEMPTS){
            return 'attempts';
        }
        if(empty($login->log_code_expires) || strtotime($login->log_code_expires) < time()){
            return 'expired';
        }
        if(strlen($code) !== 6 || !password_verify($code, $login->log_token)){
            $loginDAO = new LoginDAO();
            $loginDAO->addCodeAttempt($login->log_id);
            return 'code';
        }
        return null;
    }

    private function resendWait($flow){
        return max(0, self::RESEND_SECONDS - (time() - $_SESSION[$flow]['sent_at']));
    }

    // Load the View Verify-email
    public function verifyEmail(){
        if(empty($_SESSION['verify'])){
            header('Location: /sign-in');
            exit;
        }
        $this->getView()->title = "EMPP";
        $this->getView()->title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->verify = $_SESSION['verify'];
        $this->getView()->resendIn = $this->resendWait('verify');
        $this->getView()->minutes = self::CODE_MINUTES;

        $this->render('verify_email', '');
    }

    public function actionVerifyEmail(){
        if(empty($_SESSION['verify'])){
            header('Location: /sign-in');
            exit;
        }
        $code = preg_replace('/\D/', '', $_POST['code'] ?? '');

        $loginDAO = new LoginDAO();
        $login = $loginDAO->searchById($_SESSION['verify']['log_id']);
        if(!$login){
            unset($_SESSION['verify']);
            header('Location: /sign-in');
            exit;
        }
        if($login->log_confirmed == 'Y'){
            unset($_SESSION['verify']);
            header('Location: /sign-in?verified=1');
            exit;
        }
        $error = $this->checkCode($login, $code);
        if($error){
            header("Location: /verify-email?error=$error");
            exit;
        }

        $loginDAO->confirmEmail($login->log_id);
        unset($_SESSION['verify']);
        header('Location: /sign-in?verified=1');
        exit;
    }

    public function actionResendCode(){
        if(empty($_SESSION['verify'])){
            header('Location: /sign-in');
            exit;
        }
        if($this->resendWait('verify') > 0){
            header('Location: /verify-email');
            exit;
        }
        $sent = $this->sendCode('verify');
        header('Location: /verify-email?' . ($sent ? 'resent=1' : 'error=send'));
        exit;
    }

    // Password reset -----------------------------------------------------------

    public function actionForgotPassword(){
        $email = trim($_POST['email'] ?? '');
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            header('Location: /forgot-password?error=email');
            exit;
        }
        // Asking again for the same email within the wait only reopens the code page
        if(!empty($_SESSION['reset']) && $_SESSION['reset']['email'] === $email && $this->resendWait('reset') > 0){
            header('Location: /reset-password');
            exit;
        }

        $loginDAO = new LoginDAO();
        $login = $loginDAO->searchAnyByEmail($email);
        $_SESSION['reset'] = [
            'log_id' => $login ? $login->log_id : null,
            'email' => $email,
            'name' => $login ? $login->res_name : '',
            'sent_at' => 0,
        ];
        $sent = $this->sendCode('reset');
        header('Location: /reset-password' . ($sent ? '' : '?error=send'));
        exit;
    }

    // Load the View Reset-password
    public function resetPassword(){
        if(empty($_SESSION['reset'])){
            header('Location: /forgot-password');
            exit;
        }
        $this->getView()->title = "EMPP";
        $this->getView()->title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->reset = $_SESSION['reset'];
        $this->getView()->resendIn = $this->resendWait('reset');
        $this->getView()->minutes = self::CODE_MINUTES;

        $this->render('reset_password', '');
    }

    public function actionResetPassword(){
        if(empty($_SESSION['reset'])){
            header('Location: /forgot-password');
            exit;
        }
        $code = preg_replace('/\D/', '', $_POST['code'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if($password === ''){
            header('Location: /reset-password?error=password-empty');
            exit;
        }
        if($password !== $passwordConfirm){
            header('Location: /reset-password?error=password-match');
            exit;
        }

        $loginDAO = new LoginDAO();
        $login = empty($_SESSION['reset']['log_id']) ? null : $loginDAO->searchById($_SESSION['reset']['log_id']);
        if(!$login){
            // Unknown email: same answer a wrong code gets
            header('Location: /reset-password?error=code');
            exit;
        }
        $error = $this->checkCode($login, $code);
        if($error){
            header("Location: /reset-password?error=$error");
            exit;
        }

        $global = new FuncoesGlobais();
        $update = new LoginModel();
        $update->log_id = $login->log_id;
        $update->log_password = $global->hashSenha($password);
        $loginDAO->updatePassword($update);
        // The code proved the email belongs to them, so it also counts as confirmed
        $loginDAO->confirmEmail($login->log_id);

        unset($_SESSION['reset'], $_SESSION['verify']);
        header('Location: /sign-in?reset=1');
        exit;
    }

    public function actionResendResetCode(){
        if(empty($_SESSION['reset'])){
            header('Location: /forgot-password');
            exit;
        }
        if($this->resendWait('reset') > 0){
            header('Location: /reset-password');
            exit;
        }
        $sent = $this->sendCode('reset');
        header('Location: /reset-password?' . ($sent ? 'resent=1' : 'error=send'));
        exit;
    }



    public function actionSignIn(){
        $email = $_POST['email-username'];
        $password = $_POST['password'];

        // Validate the data
        if(!empty($email) && !empty($password)){

            $global = new FuncoesGlobais();
            $loginDAO = new LoginDAO();
            $login = $loginDAO->searchByEmail($email);

            if($login && $global->verificarSenha($password, $login->log_password)){
                // Accounts still on the legacy SHA-1 hash move to bcrypt on this login
                if($global->senhaPrecisaRehash($login->log_password)){
                    $rehash = new LoginModel();
                    $rehash->log_id = $login->log_id;
                    $rehash->log_password = $global->hashSenha($password);
                    $loginDAO->updatePassword($rehash);
                }
                // A researcher signs in only after confirming the email: send a new code
                if($login->log_type == 'R' && $login->log_confirmed != 'Y'){
                    $this->startVerification($login->log_id, $login->log_email, $login->res_name);
                    exit;
                }
                session_regenerate_id(true);
                $_SESSION['log_id'] = $login->log_id;
                $_SESSION['log_type'] = $login->log_type;
                $_SESSION['log_status'] = $login->log_status;
                $_SESSION['res_photo'] = $login->res_photo;
                if($_SESSION['log_type'] == 'A'){
                    header('Location: /dashboard/administrator');
                }else{
                    header('Location: /dashboard/researcher');
                }
                exit;
            }
        }

        // Same answer for unknown email and wrong password
        header('Location: /sign-in?error=1');
        exit;
    }
    public function actionSignOut(){
        session_destroy();
        header('Location: /sign-in');
    }
    
    
    

    public function validaAutenticacao() {

        
    }
}
