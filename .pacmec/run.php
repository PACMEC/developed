<?php
/**
 *
 * @author     FelipheGomez <feliphegomez@gmail.com>
 * @package    PACMEC
 * @category   System
 * @copyright  2020-2021 Manager Technology CO
 * @license    license.txt
 * @version    Release: @package_version@
 * @link       http://github.com/ManagerTechnologyCO/PACMEC
 * @version    1.0.1
 */

if (!defined('PACMEC_PATH')) define('PACMEC_PATH', __DIR__ . '/');

require_once PACMEC_PATH . '.prv/settings.php';   // Configuración global
require_once PACMEC_PATH . 'functions.php';       // Funciones globales
require_once PACMEC_PATH . '.prv/autoClass.php';  // Deteccion auto de clases
require_once PACMEC_PATH . '.prv/sm.php';  // Deteccion auto de clases

pacmec_init_header();
pacmec_init_vars();
pacmec_init_session();
pacmec_init_options();
pacmec_init_system();
pacmec_init_setup();
pacmec_init_plugins_actives();
pacmec_init_route();
pacmec_validate_route();

if(siteinfo('enable_ssl') == 1 && $_SERVER["HTTPS"] != "on"){
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit("Redireccionando...");
}



pacmec_theme_check();
pacmec_assets_globals();
pacmec_run_ui();
