<?php

namespace App;

use FW\Init\Boostrap;
use FW\Router\RouteManager;

class Route extends Boostrap
{

    public function initRoutes()
    {

        //Não excluir a Rota abaixo
        $routes['error-404'] = array(
            'route' => '/error404',
            'controller' => 'ErrorController',
            'action' => 'error404'
        );

        // Administrator oversight routes, kept in code so they ship with the
        // controller instead of depending on rows in the `routes` table.
        $routes['Administrator_researcher_view'] = array(
            'route' => '/dashboard/administrator/researchers/{id}',
            'controller' => 'AdministratorController',
            'action' => 'adminResearcherView',
            'is_dynamic' => 1,
            'pattern' => 'dashboard/administrator/researchers/{id}'
        );
        $routes['Administrator_dataset'] = array(
            'route' => '/dashboard/administrator/dataset',
            'controller' => 'AdministratorController',
            'action' => 'adminDataset'
        );
        // Any signed-in user saving their own profile (the older update-profile
        // route is the administrator editing someone else's profile)
        $routes['Account_update_profile'] = array(
            'route' => '/dashboard/account/update-profile',
            'controller' => 'AdministratorController',
            'action' => 'actionUpdateMyProfile'
        );
        $routes['Administrator_dataset_download'] = array(
            'route' => '/dashboard/administrator/dataset/download',
            'controller' => 'AdministratorController',
            'action' => 'adminDatasetDownload'
        );



        $routeManager = RouteManager::getInstance();
        $dbRoutes = $routeManager->getAllRoutes();

        
        foreach ($dbRoutes as $dbRoute) {
            $routes[$dbRoute['nome_rota']] = array(
                'route' => '/' . $dbRoute['slug'],
                'controller' => $dbRoute['controller'],
                'action' => $dbRoute['action'],
                'is_dynamic' => $dbRoute['is_dynamic'],
                'pattern' => $dbRoute['pattern'] ?? null
            );
        }
        
        

        $this->setRoutes($routes);
    }
}
