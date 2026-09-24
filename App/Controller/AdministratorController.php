<?php

namespace App\Controller;

use FW\Controller\FuncoesGlobais;

use FW\Controller\Action;
use App\DAO\ResearcherDAO;
use App\Model\ResearcherModel;
use App\DAO\CountryDAO;
use FontLib\Table\Type\head;
use App\DAO\LoginDAO;
use App\Model\LoginModel;
use App\DAO\ResearchDAO;
use App\Model\ResearchModel;
use App\DAO\Research_ResultsDAO;
use App\Model\Research_ResultsModel;

use App\DAO\Filter_SimulationDAO;
use App\Model\Filter_SimulationModel;
use App\Model\SimulationModel;
use App\DAO\SimulationDAO;

class AdministratorController extends Action{


    public function logged(){
        $researcherDAO = new ResearcherDAO();
        $researcher = $researcherDAO->searchByLogId($_SESSION['log_id']);

        $this->getView()->researcher = $researcher;
        $this->getView()->is_admin = $this->isAdmin();

    }

    /* Access control
     * Administrators are researchers with oversight: they can use every
     * researcher screen and, on top of that, the administrative ones. */
    private function isAdmin(){
        return isset($_SESSION['log_type']) && $_SESSION['log_type'] === 'A';
    }

    private function requireLogin(){
        if(empty($_SESSION['log_id'])){
            header('Location: /sign-in');
            exit;
        }
    }

    private function requireAdmin(){
        $this->requireLogin();
        if(!$this->isAdmin()){
            header('Location: /dashboard/researcher');
            exit;
        }
    }

    // A research study is visible to its owner and to administrators.
    private function requireResearchAccess($research){
        if(!$research || (!$this->isAdmin() && $research->__get('fk_login_log_id') != $_SESSION['log_id'])){
            header('Location: /dashboard/researcher/research-studies');
            exit;
        }
    }
    /* Administrative Methods */
    // Load the View Login
    public function dashboard(){
        $this->requireAdmin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "overview";
        $this->getView()->active_page = $active_page;


        $this->logged();

        $researchersDAO = new ResearcherDAO();
        $total_researchers = $researchersDAO->countResearchers();
        $this->getView()->total_researchers = $total_researchers;

        $researchDAO = new ResearchDAO();
        $total_research = $researchDAO->countResearch();
        $this->getView()->total_research = $total_research;

        // Latest activity across all researchers
        $this->getView()->recent = array_slice($researchDAO->select(), 0, 8);


        $this->render('index', 'dashboard');
    }

    public function administratorMyProfile(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $this->getView()->active_page = "";

        $this->logged();

        $countryDAO = new CountryDAO();
        $countries = $countryDAO->select();
        $this->getView()->countries = $countries;

        $this->render('my_profile', 'dashboard');
    }

    public function actionUpdateMyProfile(){
        $this->requireLogin();
        
        $global = new FuncoesGlobais();
        // Always the signed-in user's own profile, never an id from the form
        $id = (new ResearcherDAO())->searchByLogId($_SESSION['log_id'])->res_id;
        $name = $_POST['name'];
        $institution = $_POST['institution'];
        $purpose = $_POST['purpose'];
        $academich = $_POST['academic'];
        if($academich == "other"){
            $academich = trim($_POST['specific'] ?? '');
        }
        $birth = $_POST['birth'] ?? null;
        
        $country = $_POST['country'];

        $researcherDAO = new ResearcherDAO();
        $researcherModel = new ResearcherModel();
        $researcherModel->res_id = $id;
        $researcherModel->res_name = $name;
        $researcherModel->res_institution = $institution;
        $researcherModel->res_purpose = $purpose;
        $researcherModel->res_academic = $academich;
        $researcherModel->res_birth = $birth;
        $researcherModel->fk_country_cou_id = $country;
        
        if($researcherDAO->update($researcherModel)){
            header("Location: /dashboard/administrator/my-profile?success=1");
        }else{
            header("Location: /dashboard/administrator/my-profile?success=0");
        }
        
    }


    public function updateMyPassword(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $this->getView()->active_page = "";

        $this->logged();
        $this->render('my_password', 'dashboard');
    }


    public function actionUpdateMyPassword(){
        $this->requireLogin();
        $global = new FuncoesGlobais();
        // Always the signed-in user's own password, never an id from the form
        $id = $_SESSION['log_id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if($current_password != "" && $new_password != "" && ($new_password === $confirm_new_password)){
            $loginDAO = new LoginDAO();
            $password = $loginDAO->searchById($id)->log_password;
            if ($global->verificarSenha($current_password, $password)) {
                $loginModel = new LoginModel();
                $loginModel->log_id = $id;
                $loginModel->log_password = $global->hashSenha($new_password);
                if($loginDAO->updatePassword($loginModel)){
                    header("Location: /dashboard/administrator/my-password?success=1");
                    
                }else{
                    header("Location: /dashboard/administrator/my-password?success=2");
                }
            }else{
                header("Location: /dashboard/administrator/my-password?success=0");
            }
        }else{
            // Empty current password or new/confirmation mismatch
            header("Location: /dashboard/administrator/my-password?success=3");
        }
    }

    public function uploadMyPhoto(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $this->getView()->active_page = "";

        $this->logged();
        $this->render('my_photo', 'dashboard');
    }


    public function uploadPhoto(){      
        $this->requireLogin();
        $this->render('upload', '');
    }

    public function updateMyPhoto(){
        $this->requireLogin();
        try{
            $researcherDAO = new ResearcherDAO();
            if($researcherDAO->updatePhoto($_SESSION['log_id'], $_SESSION['photo'])){
                $_SESSION['res_photo'] = $_SESSION['photo'];
                header("Location: /dashboard/administrator/my-photo?success=1");
            }else{
                header("Location: /dashboard/administrator/my-photo?success=0");
            }
        }catch(\Exception $e){
            echo "Error selecting data: " . $e->getMessage();
        }
    }

    // Load the View Login
    public function pageResearchers(){
        $this->requireAdmin();
        $title = "Registered Researchers";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "researchers";
        $this->getView()->active_page = $active_page;


        $this->logged();


        $researcherDAO = new ResearcherDAO();
        $researchers = $researcherDAO->select();
        $this->getView()->researchers = $researchers;

        $this->render('researchers', 'dashboard');
    }

    public function researcherFormEdit(){
        $this->requireAdmin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "researchers";
        $this->getView()->active_page = $active_page;
        $this->logged();

        $id = $this->getParams()[1];
        
        
        $researcherDAO = new ResearcherDAO();
        $researchers = $researcherDAO->searchByLogId($id);
        $this->getView()->researchers = $researchers;
       

        $countryDAO = new CountryDAO();
        $countries = $countryDAO->select();
        $this->getView()->country = $countries;

        $this->render('researcher_edit', 'dashboard');
    }


    public function updateProfile(){
        $this->requireAdmin();
        $global = new FuncoesGlobais();
        $id = $_POST['res_id'];
        $name = $_POST['name'];
        $institution = $_POST['institution'];
        $purpose = $_POST['purpose'];
        $academic = $_POST['academic'];
        if($academic == "other"){
            $academic = $_POST['specific'];
        }
        $birth = $_POST['birth'] ?? null;
        
        $country = $_POST['country'];

        $researcherDAO = new ResearcherDAO();
        $researcherModel = new ResearcherModel();
        $researcherModel->res_id = $id;
        $researcherModel->res_name = $name;
        $researcherModel->res_institution = $institution;
        $researcherModel->res_purpose = $purpose;
        $researcherModel->res_academic = $academic;
        $researcherModel->res_birth = $birth;
        $researcherModel->fk_country_cou_id = $country;
        

        if($researcherDAO->update($researcherModel)){
            header("Location: /dashboard/administrator/researchers?success=1");
        }else{
            header("Location: /dashboard/administrator/researchers?success=0");
        }
    }

    public function pageResearchs(){
        $this->requireAdmin();
        $title = "Research";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "all_research";
        $this->getView()->active_page = $active_page;

        $this->logged();

        $researchDAO = new ResearchDAO();
        $researchs = $researchDAO->select();
        $this->getView()->researchs = $researchs;

        $this->render('research', 'dashboard');

    }

    /* END Administrative Methods */

    /* Research Methods */

    public function dashboardResearcher(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "dashboard";
        $this->getView()->active_page = $active_page;


        $this->logged();

        // The researcher's own work, not platform-wide totals
        $this->getView()->researchs = (new ResearchDAO())->searchMyResearch($_SESSION['log_id']);

        $this->render('res_dashboard', 'dashboard');
    }

    public function researchStudies(){
        $this->requireLogin();
        $title = "Research";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;

        $this->logged();

        $researchDAO = new ResearchDAO();
        $researchs = $researchDAO->searchMyResearch($_SESSION['log_id']);
        $this->getView()->researchs = $researchs;

        $this->render('res_research', 'dashboard');

    }



    public function newResearch(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;


        $this->logged();


        $this->render('res_new_research_file', 'dashboard');
    }
    
    public function researchUpload(){
        $this->requireLogin();
        
        $this->render('upload_research', '',$_ENV['BASE_URL']. 'vendor/autoload.php');
        
    }
    
    public function researchInfo(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;


        $this->logged();


        $this->render('res_new_research', 'dashboard');
    }
    
    public function researchInsert(){
        $this->requireLogin();
        $ree_name = $_POST['ree_name'];
        $ree_description = $_POST['ree_description'];
        // The image is the one this session uploaded (upload_research.php names it
        // <md5>.jpg), never a file name from the form: the name ends up in pages
        // other users open, including administrators.
        $ree_file = $_SESSION['ree_file'] ?? '';
        if (!preg_match('/^[a-f0-9]{32}\.jpg$/', $ree_file)) {
            header("Location: /dashboard/researcher/research/new");
            exit;
        }
        $ree_flow = $_POST['ree_flow'];
        $ree_voltage = $_POST['ree_voltage'];
        $ree_distance = $_POST['ree_distance'];
        // Research belongs to whoever is signed in, never to an id from the form
        $fk_login_log_id = $_SESSION['log_id'];

        // Process parameters: positive numbers with a point as decimal separator
        foreach (['ree_flow', 'ree_voltage', 'ree_distance'] as $field) {
            $$field = trim((string) $$field);
            if (!is_numeric($$field) || $$field <= 0) {
                header("Location: /dashboard/researcher/research/info?error=number");
                exit;
            }
        }



        $researchModel = new ResearchModel();
        $researchModel->__set('ree_name', $ree_name);
        $researchModel->__set('ree_description', $ree_description);
        $researchModel->__set('ree_file', $ree_file);
        $researchModel->__set('ree_flow', $ree_flow);
        $researchModel->__set('ree_voltage', $ree_voltage);
        $researchModel->__set('ree_distance', $ree_distance);
        $researchModel->__set('fk_login_log_id', $fk_login_log_id);




        $researchDAO = new ResearchDAO();
        $researchId = $researchDAO->create($researchModel);
        if($researchId){
            // One upload, one study
            unset($_SESSION['ree_file']);
            header("Location: /dashboard/researcher/research/view/" . $researchId . "");
        }
    }


    public function researchInsertResult(){
        $this->requireLogin();
        
        $rre_dissimilarity = $_POST['rre_dissimilarity'];
        $rre_correlation = $_POST['rre_correlation'];
        $rre_energy = $_POST['rre_energy'];
        $rre_homogeneity = $_POST['rre_homogeneity'];
        $fk_research_ree_id = (int) $_POST['fk_research_ree_id'];
        $this->requireResearchAccess((new ResearchDAO())->searchById($fk_research_ree_id));

        $researchModel = new Research_ResultsModel();
        $researchModel->__set('rre_dissimilarity', $rre_dissimilarity);
        $researchModel->__set('rre_correlation', $rre_correlation);
        $researchModel->__set('rre_energy', $rre_energy);
        $researchModel->__set('rre_homogeneity', $rre_homogeneity);
        $researchModel->__set('fk_research_ree_id', $fk_research_ree_id);

        

        $researchDAO = new Research_ResultsDAO();
        if($researchDAO->create($researchModel)){
            header("Location: " . $_ENV['BASE_URL'] . "/dashboard/researcher/research/view/" . $fk_research_ree_id . "");
        }
    }



    public function researchView(){
        $this->requireLogin();
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;
        $ree_id = $this->getParams()[1];

        $this->logged();

        $researchDAO = new ResearchDAO();
        $research = $researchDAO->searchById($ree_id);
        $this->requireResearchAccess($research);
        $this->getView()->research = $research;
        // Admins can open anyone's study; processing actions stay with the owner
        $this->getView()->is_owner = $research->__get('fk_login_log_id') == $_SESSION['log_id'];
        if(!$this->getView()->is_owner){
            $this->getView()->active_page = "all_research";
        }

        $research_resultsDAO = new Research_ResultsDAO();
        $research_results = $research_resultsDAO->searchById($ree_id);
        $this->getView()->research_results = $research_results;

        $filter_simulationDAO = new Filter_SimulationDAO();
        $filter_simulation = $filter_simulationDAO->searchById($ree_id);
        if($filter_simulation) {
            $this->getView()->filter_simulation = $filter_simulation;
        }else{
            $this->getView()->filter_simulation = null;
        }

        $this->render('research_view', 'dashboard');
        
    }

    

    

    public function filterSimulationCreate(){
        $this->requireLogin();
        $ree_id = $this->getParams()[1];
        $title = "EMPP - New filter simulation ";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;

        $this->getView()->ree_id = $ree_id;
        $research = (new ResearchDAO())->searchById($ree_id);
        $this->requireResearchAccess($research);
        $this->getView()->research = $research;
        // Prefills the porosity field (fraction) from the study's prediction (%)
        $results = (new Research_ResultsDAO())->searchById($ree_id);
        $this->getView()->predicted_porosity = $results ? $results->__get('rre_porosity') : null;

        $this->logged();

        $this->render('filter_simulation_create', 'dashboard');
    }


    public function filterSimulationSave(){
        $this->requireLogin();
        $fis_material = $_POST['fis_material'];
        $fis_thickness = $_POST['fis_thickness'];
        $fis_diameter = $_POST['fis_diameter'];
        $fis_porosity = $_POST['fis_porosity'];
        $fis_temperature = $_POST['fis_temperature'];
        $fis_pressure = $_POST['fis_pressure'];
        $fis_velocity = $_POST['fis_velocity'];
        $fis_area = $_POST['fis_area'];
        $fis_density = $_POST['fis_density'];
        $fis_size_min = $_POST['fis_size_min'];
        $fis_size_max = $_POST['fis_size_max'];
        $fis_concentration = $_POST['fis_concentration'];
        $fis_class = '100';
        $fk_research_ree_id = (int) $_POST['fk_research_ree_id'];
        $this->requireResearchAccess((new ResearchDAO())->searchById($fk_research_ree_id));

        // Numbers only, with a point as decimal separator ("0,7" is rejected, not
        // converted), and within the ranges the model can compute
        foreach (['fis_thickness', 'fis_diameter', 'fis_porosity', 'fis_temperature', 'fis_pressure', 'fis_velocity',
                  'fis_area', 'fis_density', 'fis_size_min', 'fis_size_max', 'fis_concentration'] as $field) {
            $$field = trim($$field);
            if (!is_numeric($$field)) {
                header("Location: /dashboard/researcher/research/filter-simulation-create/$fk_research_ree_id?error=number");
                exit;
            }
        }
        if ($fis_porosity <= 0 || $fis_porosity >= 1) {
            header("Location: /dashboard/researcher/research/filter-simulation-create/$fk_research_ree_id?error=porosity");
            exit;
        }
        if ($fis_size_min <= 0 || $fis_size_max <= $fis_size_min || $fis_thickness <= 0 || $fis_diameter <= 0 || $fis_velocity <= 0 || $fis_pressure <= 0) {
            header("Location: /dashboard/researcher/research/filter-simulation-create/$fk_research_ree_id?error=range");
            exit;
        }

        $filter_simulationModel = new Filter_SimulationModel();
        $filter_simulationModel->__set('fis_material', $fis_material);
        $filter_simulationModel->__set('fis_thickness', $fis_thickness);
        $filter_simulationModel->__set('fis_diameter', $fis_diameter);
        $filter_simulationModel->__set('fis_porosity', $fis_porosity);
        $filter_simulationModel->__set('fis_temperature', $fis_temperature);
        $filter_simulationModel->__set('fis_pressure', $fis_pressure);
        $filter_simulationModel->__set('fis_velocity', $fis_velocity);
        $filter_simulationModel->__set('fis_area', $fis_area);
        $filter_simulationModel->__set('fis_density', $fis_density);
        $filter_simulationModel->__set('fis_size_min', $fis_size_min);
        $filter_simulationModel->__set('fis_size_max', $fis_size_max);
        $filter_simulationModel->__set('fis_concentration', $fis_concentration);
        $filter_simulationModel->__set('fis_class', $fis_class);
        $filter_simulationModel->__set('fk_research_ree_id', $fk_research_ree_id);

        $filter_simulationDAO = new Filter_SimulationDAO();
        $fis_id = $filter_simulationDAO->create($filter_simulationModel);
        if($fis_id){

            $simulationModel = new SimulationModel();

            foreach ($this->deriveSimulationProperties($fis_porosity, $fis_diameter, $fis_pressure, $fis_temperature) as $column => $value) {
                $simulationModel->__set($column, $value);
            }
            $simulationModel->__set('fk_filter_simulation_fis_id', $fis_id);

            

            $simulationDAO = new SimulationDAO();
            $simulationDAO->create($simulationModel);

            header("Location: /dashboard/researcher/research/view/$fk_research_ree_id?success=3");
        }
    }

    /**
     * Membrane/air properties derived from the simulation inputs, keyed by the
     * `simulation` table columns. Used when saving a simulation and to rebuild
     * them for records saved with bad derived values.
     */
    private function deriveSimulationProperties($fis_porosity, $fis_diameter, $fis_pressure, $fis_temperature){
        $density = 1 - $fis_porosity;
        $permeability_k1 = (pow(($fis_diameter / 1000000), 2)) / (64 * pow((1 - $fis_porosity), 1.5) * (1 + 56 * pow((1 - $fis_porosity), 3)));
        $permeability_k2 = exp(-1.71588 * pow($permeability_k1, -0.08093));
        $gravitational_acceletration = 9.81;
        $test_pressure = ($fis_pressure / 760) * 101325;
        $air_density = ($test_pressure * 0.028965) / (8.314 * ($fis_temperature + 273));
        $air_viscosity = 1.73 * pow(10, -5) * pow(($fis_temperature + 273) / 273, 1.5) * (398 / ($fis_temperature + 398));
        $air_free_path = (21.2255 * $air_viscosity * pow((273 + $fis_temperature), 0.5)) / $test_pressure;
        $boltzmann_constant = 1.380649 * pow(10, -23);
        $kuwabara_number = (-log(1 - $fis_porosity) / 2) - (3 / 4) + (1 - $fis_porosity) - (pow((1 - $fis_porosity), 2) / 4);
        $knudsen_number = (2 * $air_free_path) / ($fis_diameter / 1000000);

        return [
            'sim_density' => $density,
            'sim_permeability_k1' => $permeability_k1,
            'sim_permeability_k2' => $permeability_k2,
            'sim_gravitational' => $gravitational_acceletration,
            'sim_air_density' => $air_density,
            'sim_air_viscosity' => $air_viscosity,
            'sim_air_free_path' => $air_free_path,
            'sim_test_pressure' => $test_pressure,
            'sim_boltzmann' => $boltzmann_constant,
            'sim_kuwabara' => $kuwabara_number,
            'sim_knudsen' => $knudsen_number,
        ];
    }

    public function FilterSimulationView(){
        $this->requireLogin();
        $title = "EMPP - Filter simulation ";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;


        $fis_id = $this->getParams()[1];
        
        $this->logged();

        $filter_simulationDAO = new Filter_SimulationDAO();
        $filter_simulation = $filter_simulationDAO->searchFilterSimulation($fis_id);
        $this->requireResearchAccess($filter_simulation ? (new ResearchDAO())->searchById($filter_simulation->__get('fk_research_ree_id')) : null);
        $this->getView()->filter_simulation = $filter_simulation;


        $simulationDAO = new SimulationDAO();
        $simulation = $simulationDAO->searchSimulationByFilterId($fis_id);

        // Records saved with comma decimals ("0,700") got their derived properties
        // computed from zeros. The inputs themselves are fine once the comma is a
        // dot, so rebuild the derived values for display (nothing is written back).
        $this->getView()->recomputed = false;
        if ($filter_simulation && $simulation) {
            $inputs = [];
            foreach (['fis_porosity', 'fis_diameter', 'fis_pressure', 'fis_temperature'] as $field) {
                $inputs[$field] = str_replace(',', '.', (string) $filter_simulation->__get($field));
            }
            $derived_broken = (float) $simulation->__get('sim_kuwabara') == 0.0
                || (float) $simulation->__get('sim_knudsen') == 0.0
                || (float) $simulation->__get('sim_permeability_k1') == 0.0;
            $inputs_ok = count(array_filter($inputs, 'is_numeric')) === count($inputs)
                && $inputs['fis_porosity'] > 0 && $inputs['fis_porosity'] < 1
                && $inputs['fis_diameter'] > 0 && $inputs['fis_pressure'] > 0;
            if ($derived_broken && $inputs_ok) {
                $derived = $this->deriveSimulationProperties($inputs['fis_porosity'], $inputs['fis_diameter'], $inputs['fis_pressure'], $inputs['fis_temperature']);
                foreach ($derived as $column => $value) {
                    $simulation->__set($column, $value);
                }
                $this->getView()->recomputed = true;
            }
        }
        $this->getView()->simulation = $simulation;

        $this->render('filter_simulation_view', 'dashboard');
    }


    public function getDistribution(){
        $this->requireLogin();
        $ree_id = $this->getParams()[1];
        $title = "EMPP";
        $this->getView()->title = $title;
        $title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->title_page = $title_page;
        $active_page = "research";
        $this->getView()->active_page = $active_page;

        $this->logged();

        $this->render('filter_distribution', 'dashboard');
    }


     public function researchUpdatePorosity(){
        $this->requireLogin();
        $fk_research_ree_id = $_POST['fk_research_ree_id'];
        $this->requireResearchAccess((new ResearchDAO())->searchById($fk_research_ree_id));
        $ree_porosity = $_POST['rre_porosity'];
        

        $Research_ResultsDAO = new Research_ResultsDAO();
        if($Research_ResultsDAO->updatePorosity($fk_research_ree_id, $ree_porosity)){
            header("Location: /dashboard/researcher/research/view/$fk_research_ree_id?success=4");
        }else{
            header("Location: /dashboard/researcher/research/view/$fk_research_ree_id?success=0");
        }
    }

    /* Administrative oversight */

    // One researcher: profile plus all of their research studies
    public function adminResearcherView($matches = []){
        $this->requireAdmin();
        $log_id = (int) ($matches['id'] ?? 0);

        $this->getView()->title = "Researcher";
        $this->getView()->title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->active_page = "researchers";
        $this->logged();

        $profile = (new ResearcherDAO())->searchByLogId($log_id);
        if(!$profile){
            header('Location: /dashboard/administrator/researchers');
            exit;
        }
        $this->getView()->profile = $profile;
        $this->getView()->researchs = (new ResearchDAO())->searchMyResearch($log_id);

        $this->render('researcher_view', 'dashboard');
    }

    // Image dataset: what is available for model training, with the export
    public function adminDataset(){
        $this->requireAdmin();

        $this->getView()->title = "Image Dataset";
        $this->getView()->title_page = "EMPP - Electrospun Membrane Property Predictor";
        $this->getView()->active_page = "dataset";
        $this->logged();

        $rows = (new ResearchDAO())->selectDataset();
        $stats = ['studies' => 0, 'images' => 0, 'features' => 0, 'predicted' => 0];
        $by_researcher = [];
        foreach($rows as $row){
            $has_image = $this->datasetImagePath($row['ree_file']) !== null;
            $has_features = $row['rre_dissimilarity'] !== null;
            $has_porosity = $row['rre_porosity'] !== null && $row['rre_porosity'] !== '';

            $stats['studies']++;
            $stats['images'] += $has_image ? 1 : 0;
            $stats['features'] += $has_features ? 1 : 0;
            $stats['predicted'] += $has_porosity ? 1 : 0;

            $id = $row['fk_login_log_id'];
            if(!isset($by_researcher[$id])){
                $by_researcher[$id] = ['log_id' => $id, 'name' => $row['res_name'], 'institution' => $row['res_institution'],
                                       'studies' => 0, 'images' => 0, 'features' => 0];
            }
            $by_researcher[$id]['studies']++;
            $by_researcher[$id]['images'] += $has_image ? 1 : 0;
            $by_researcher[$id]['features'] += $has_features ? 1 : 0;
        }
        $this->getView()->stats = $stats;
        $this->getView()->by_researcher = array_values($by_researcher);

        $this->render('dataset', 'dashboard');
    }

    // ZIP with every SEM image + dataset.csv laid out like API_PYTHON's DatasetV3.csv
    public function adminDatasetDownload(){
        $this->requireAdmin();
        set_time_limit(300);

        $log_id = isset($_GET['researcher']) && $_GET['researcher'] !== '' ? (int) $_GET['researcher'] : null;
        $rows = (new ResearchDAO())->selectDataset($log_id);

        $zip = new \PhpZip\ZipFile();
        $csv = fopen('php://temp', 'r+');

        // First 13 columns match DatasetV3.csv (read by mlops.py); the rest is provenance.
        // "Porosidade" is the MEASURED porosity and is left empty on purpose: the
        // platform only stores the model's prediction, which must not be used as a label.
        fputcsv($csv, [
            'File', 'Composição', 'Vazão da Seringa', 'Tensão', 'Distância', 'Rotação', 'Translação',
            'Porosidade', 'Tamanho de Poro', 'Dissimilarity', 'Correlation', 'Energy', 'Homogeinity',
            'Research ID', 'Study name', 'Researcher', 'Institution', 'Created at',
            'Predicted porosity (EMPP model)', 'Image included',
        ], ',', '"', '');

        foreach($rows as $row){
            $path = $this->datasetImagePath($row['ree_file']);
            $entry = 'images/' . $row['ree_id'] . '_' . basename($row['ree_file']);
            if($path !== null){
                // SEM images are already compressed; storing them is much faster
                $zip->addFile($path, $entry, \PhpZip\Constants\ZipCompressionMethod::STORED);
            }
            fputcsv($csv, [
                $entry, '', $row['ree_flow'], $row['ree_voltage'], $row['ree_distance'], '', '',
                '', '', $row['rre_dissimilarity'], $row['rre_correlation'], $row['rre_energy'], $row['rre_homogeneity'],
                $row['ree_id'], $row['ree_name'], $row['res_name'], $row['res_institution'], $row['ree_create'],
                $row['rre_porosity'], $path !== null ? 'yes' : 'no',
            ], ',', '"', '');
        }
        rewind($csv);
        $zip->addFromString('dataset.csv', stream_get_contents($csv));
        fclose($csv);

        $zip->addFromString('README.txt', implode("\n", [
            'EMPP image dataset',
            'Exported ' . date('Y-m-d H:i') . ' - ' . count($rows) . ' research studies',
            '',
            'images/      SEM images, named <research id>_<original file name>',
            'dataset.csv  one row per study. The first 13 columns follow',
            '             API_PYTHON/nanofiber-porosity-api-main/data/DatasetV3.csv,',
            '             so rows can be appended to it and used by mlops.py.',
            '',
            'Before training:',
            '- "Porosidade" is empty. Fill it with the MEASURED porosity of each sample.',
            '  "Predicted porosity (EMPP model)" is the current model\'s output; training',
            '  on it would only teach the model its own predictions.',
            '- Rows without Haralick features (empty Dissimilarity..Homogeinity) were never',
            '  processed on the platform; run the feature extraction on their images first.',
            '- Composição, Rotação, Translação and Tamanho de Poro are not collected by the',
            '  platform. mlops.py does not use them.',
            '',
        ]));

        $suffix = '';
        if($log_id !== null && $rows){
            // Transliteration may leave accent marks as separate chars (á -> 'a)
            $ascii = str_replace(["'", '"', '`', '^', '~'], '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $rows[0]['res_name']));
            $suffix = '-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)), '-');
        }
        $zip->outputAsAttachment('empp-dataset-' . date('Ymd') . $suffix . '.zip');
        $zip->close();
        exit;
    }

    // Absolute path of a research image on disk, or null when the file is missing
    private function datasetImagePath($file){
        if($file === null || $file === ''){
            return null;
        }
        $path = 'resources/dashboard/assets/img/research/' . basename($file);
        return is_file($path) ? $path : null;
    }

    public function validaAutenticacao() {

        
    }
}
