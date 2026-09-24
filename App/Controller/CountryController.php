<?php

namespace App\Controller;

use FW\Controller\Action;



class CountryController extends Action{

    public function index(){
        $title = "EMPP";
        $title_pagina = "EMPP - Electrospun Membrane Property Predictor";

        

        $this->getView()->title = $title;
        $this->getView()->title_pagina = $title_pagina;

        $this->render('index', 'dashboard');
    }
    
    
    

    public function validaAutenticacao() {

        
    }
}
