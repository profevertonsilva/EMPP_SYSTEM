<?php

namespace App\Controller;

use FW\Controller\Action;
use App\DAO\SiteContentDAO;

// Public site: the home at / and the page about the research at /research
class SiteController extends Action{

    public function index(){
        $this->prepare();
        $this->getView()->title_page = "EMPP — From an SEM image to filtration performance";
        $this->getView()->study = require 'App/View/site/sample_study.php';
        $this->render('index', '');
    }

    public function research(){
        $this->prepare();
        $this->getView()->title_page = "The research — EMPP";
        $this->render('research', '');
    }

    private function prepare(){
        // Signed-in visitors get a way straight back into the tool
        $this->getView()->logged = !empty($_SESSION['log_id']);
        $this->getView()->dashboard_url = (($_SESSION['log_type'] ?? '') === 'A')
            ? '/dashboard/administrator'
            : '/dashboard/researcher';

        // Team, institutions, publications, projects, funding and texts, from System settings
        $this->getView()->research = (new SiteContentDAO())->publicContent();
    }

    public function validaAutenticacao() {
    }
}
