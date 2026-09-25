<?php

namespace App\Controller;

use FW\Controller\Action;

// Public site: the page anyone lands on at /
class SiteController extends Action{

    public function index(){
        $this->getView()->title_page = "EMPP — From an SEM image to filtration performance";

        // Signed-in visitors get a way straight back into the tool
        $this->getView()->logged = !empty($_SESSION['log_id']);
        $this->getView()->dashboard_url = (($_SESSION['log_type'] ?? '') === 'A')
            ? '/dashboard/administrator'
            : '/dashboard/researcher';

        $this->getView()->research = require 'App/View/site/research.php';
        $this->getView()->study = require 'App/View/site/sample_study.php';

        $this->render('index', '');
    }

    public function validaAutenticacao() {
    }
}
