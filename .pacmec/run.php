<?php
/**
 *
 * @package    PACMEC
 * @category   System
 * @copyright  2020-2021 Manager Technology CO & FelipheGomez CO
 * @author     FelipheGomez <feliphegomez@gmail.com>
 * @license    license.txt
 * @version    0.0.1
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('PACMEC_PATH')) define('PACMEC_PATH', __DIR__);                   // Path PACMEC
require_once PACMEC_PATH . '/.prv/settings.php';                                // configuraciones principales del sitio
require_once PACMEC_PATH . '/.prv/includes.php';                                // incluir archivos

$pacmec = \PACMEC\System\Run::exec();

echo json_encode($GLOBALS['PACMEC'], JSON_PRETTY_PRINT);



  // $PACMEC = \PACMEC\Exec();
  /*
  if(MODE_DEBUG == true)
  {
    "-FINISH-";
  }

  require_once PACMEC_PATH . '.prv/sm.php';                                      // Solvemedia def*/

  /*
	foreach(\glob(PACMEC_PATH."includes/init/*.php") as $file){
		require_once $file;
		$classNameFile = \basename($file);
		$className = \str_replace([".php"],'', $classNameFile);
		if(
      !\class_exists('PACMEC\\'.$className) && !\interface_exists('PACMEC\\'.$className)
    ){
			throw new \Exception("Clase no encontrada {$className}", 1);
		}
	}
	foreach(\glob(PACMEC_PATH."includes/models/*.php") as $file){
		require_once $file;
		$classNameFile = \basename($file);
		$className = \str_replace([".php"],'', $classNameFile);
		if(
       !\class_exists('PACMEC\\'.$className) && !\interface_exists('PACMEC\\'.$className)
    ){
      throw new \Exception("Clase no encontrada {$className}", 1);
		}
	}
  if(!\pacmec_init_vars()){ throw new \Exception("pacmec_init_vars_fail", 1); }
  if(!\pacmec_init_system()){ throw new \Exception("pacmec_init_system_fail", 1); }
  if(!\pacmec_init_options()){ throw new \Exception("pacmec_init_options_fail", 1); }
  $GLOBALS['PACMEC']['session'] = \pacmec_init_session();
    if(MODE_DEBUG == true)
    {
      echo ("-- END --"."\n");
      echo \json_encode($GLOBALS['PACMEC'], JSON_PRETTY_PRINT);
    }
  */

# ();
# pacmec_init_setup();
# pacmec_init_plugins_actives();
# pacmec_init_route();
# pacmec_validate_route();
#
# if(siteinfo('enable_ssl') == 1 && $_SERVER["HTTPS"] != "on"){
#     header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
#     exit("Redireccionando...");
# }

# pacmec_theme_check();
# pacmec_assets_globals();
# pacmec_run_ui();
