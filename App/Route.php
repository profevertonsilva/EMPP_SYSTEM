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

        // Public site. Declared before the database routes so / opens the site,
        // not the login, whatever the routes table maps it to.
        $routes['Site_home'] = array(
            'route' => '/',
            'controller' => 'SiteController',
            'action' => 'index'
        );

        // Researcher email verification after sign-up (6-digit code)
        $routes['Login_verify_email'] = array(
            'route' => '/verify-email',
            'controller' => 'LoginController',
            'action' => 'verifyEmail'
        );
        $routes['Login_verify_email_action'] = array(
            'route' => '/verify-email/confirm',
            'controller' => 'LoginController',
            'action' => 'actionVerifyEmail'
        );
        $routes['Login_verify_email_resend'] = array(
            'route' => '/verify-email/resend',
            'controller' => 'LoginController',
            'action' => 'actionResendCode'
        );

        // Password reset with a 6-digit code (the /forgot-password form posts here)
        $routes['Login_forgot_password_send'] = array(
            'route' => '/forgot-password/send',
            'controller' => 'LoginController',
            'action' => 'actionForgotPassword'
        );
        $routes['Login_reset_password'] = array(
            'route' => '/reset-password',
            'controller' => 'LoginController',
            'action' => 'resetPassword'
        );
        $routes['Login_reset_password_action'] = array(
            'route' => '/reset-password/confirm',
            'controller' => 'LoginController',
            'action' => 'actionResetPassword'
        );
        $routes['Login_reset_password_resend'] = array(
            'route' => '/reset-password/resend',
            'controller' => 'LoginController',
            'action' => 'actionResendResetCode'
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
        // System settings: research content for the public page /research
        $routes['Administrator_settings'] = array(
            'route' => '/dashboard/administrator/settings',
            'controller' => 'AdministratorController',
            'action' => 'adminSettings'
        );
        $routes['Administrator_settings_save'] = array(
            'route' => '/dashboard/administrator/settings/save',
            'controller' => 'AdministratorController',
            'action' => 'adminSettingsSave'
        );
        // Public page about the research
        $routes['Site_research'] = array(
            'route' => '/research',
            'controller' => 'SiteController',
            'action' => 'research'
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
