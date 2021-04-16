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

namespace PACMEC\System;

#use \PACMEC\System\Route as Route;

class Run
{
  public function __construct()
  {
    // Crear conexion
    // Validar tablas principales
    // Tablas completas?
    // Que falta?
    //
    # echo "PACMEC\Systemx\Run::__construct()\n";
    Self::pacmec_init_files_includes();
    Self::pacmec_create_globals_vars();
    Self::pacmec_init_setup();
    Self::pacmec_init_options();
    Self::pacmec_init_plugins_actives();
    /*
    \session_set_save_handler(new \PACMEC\System\SysSession(), true);
    if(Self::is_session_started() === FALSE || \session_id() === "") \session_start();*/
  	//$GLOBALS['PACMEC']['session'] = new \PACMEC\System\Session();
  }

  public static function pacmec_init_plugins_actives()
  {
    global $PACMEC;
    $path = PACMEC_PATH."/plugins";
    echo "pacmec_init_plugins_actives"."\n";
    echo "path: {$path}"."\n";
    $plugins_activateds = explode(',', \siteinfo('plugins_activated'));
    echo "plugins_activateds: ".json_encode($plugins_activateds)."\n";

    foreach($plugins_activateds as $p){
      $path_plugin = null;

      if(is_dir("{$path}/{$p}")){
        $path_plugin = "{$path}/{$p}/{$p}.php";
      } else if(is_file("{$path}/{$p}.php")){
        $path_plugin = "{$path}/{$p}.php";
      }

      if(is_file($path_plugin)){
        //$file_type = Self::validate_type_file($file_path);
        $file_info = Self::validate_file($path_plugin);

        echo "file_info: ".json_encode($file_info)."\n";


        if(isset($file_info['plugin_name'])){
          $PACMEC['plugins'][$file_info['text_domain']] = $file_info;
          $PACMEC['plugins'][$file_info['text_domain']]['active'] = false;
          $PACMEC['plugins'][$file_info['text_domain']]['path'] = dirname($path_plugin);
          $PACMEC['plugins'][$file_info['text_domain']]['file'] = ($path_plugin);

          if(is_file($PACMEC['plugins'][$file_info['text_domain']]['file'])){
      			require_once $path_plugin;
            \activation_plugin($file_info['text_domain']);
            $PACMEC['plugins'][$file_info['text_domain']]['active'] = true;
      		}
        } else {
          \PACMEC\System\Alert::addAlert([
            "type"        => "error",
            "plugin"      => "system",
            "message"     => "El plugin {$p}, no tiene el formato correcto.\n",
            "actions"  => [
              [
                "name" => "plugins-errors",
                "plugin" => $p,
                "slug" => "/?c=admin&a=plugins&p={$p}&tab=errors_logs",
                "text" => "Ups error"
              ]
            ],
          ]);
        }
      }
      else {
        \PACMEC\System\Alert::addAlert([
          "type"        => "error",
          "plugin"      => "system",
          "message"     => "Hay problemas para cargar un plugin {$p}, quiere hacer una revision?\n",
          "actions"  => [
            [
              "name" => "plugins-errors",
              "plugin" => $p,
              "slug" => "/?c=admin&a=plugins&p={$p}&tab=errors_logs",
              "text" => "Ups error"
            ]
          ],
        ]);
      }
    }

  }

  public static function pacmec_init_files_includes()
  {
    try {
      require_once PACMEC_PATH . '/functions.php';
      require_once PACMEC_PATH . '/shortcodes.php';
    } catch (\Exception $e) {
      exit('Error en los archivos principales');
    }
  }

  public static function is_session_started() : bool
  {
      if ( \php_sapi_name() !== 'cli' ) {
          if ( \version_compare(\phpversion(), '5.4.0', '>=') ) {
              return \session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
          } else {
              return \session_id() === '' ? FALSE : TRUE;
          }
      }
      return FALSE;
  }

  /**
  * Creacion de variables globales
  *
  * @author FelipheGomez <feliphegomez@gmail.com>
  */
  public static function pacmec_create_globals_vars() : void
  {
    global $PACMEC;
    if($_SERVER['SERVER_NAME'] == PACMEC_HOST)
    {
      $PACMEC['hooks'] = \PACMEC\System\Hooks::getInstance();
      $PACMEC['DB'] = \PACMEC\System\DB::conexion();
      $PACMEC['ip'] = Self::get_ip_address();
      $PACMEC['host'] = $_SERVER['SERVER_NAME'];
      $PACMEC['fullData'] = Self::get_data_full();
      $PACMEC['lang'] = Self::get_detect_lang();
      $PACMEC['path_orig'] = \str_replace([PACMEC_HOST], '', isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI']);
      $PACMEC['path'] = \strtok($PACMEC['path_orig'], '?');
      $PACMEC['glossary'] = Self::get_langs_http();
      $PACMEC['session'] = null;
      $PACMEC['theme'] = [];
      $PACMEC['plugins'] = [];
      $PACMEC['options'] = [];
      $PACMEC['alerts'] = [];
      $PACMEC['total_records'] = [];
      //$PACMEC['route'] = new \PACMEC\System\Route();
    } else {
      throw new \Exception("Servidor no autorizado. ", 1);
    }
  }

  public static function pacmec_load_plugins($path)
  {
    if(\checkFolder($path))
    {
    	$folder_JSON = \php_file_tree_dir_JSON_exts($path, true, [], true, 0, 1);
      echo json_encode($folder_JSON)."\n";
    	foreach($folder_JSON as $file){
    		if(is_dir($file->link)){
    			$PACMEC = array_merge($PACMEC, Self::pacmec_load_plugins($file->link));
    		} else {
    			$type = Self::validate_type_file($file->link);
    			if($type == "plugin"){
    				$plugins_activated = siteinfo("plugins_activated");
    				$info = Self::validate_file($file->link);
    				if(isset($info['plugin_name'])){
    					$info['active'] = false;
    					$info['text_domain'] = (isset($info['text_domain']) ? $info['text_domain'] : str_replace(['  ',' '], ['-','-'], $info['plugin_name']));
    					$PACMEC[$info['text_domain']] = $info;
    				}
    			}
    		}
    	}
    }
  }
  public static function pacmec_init_setup()
  {
    try {
      global $PACMEC;
      $sql = "SELECT * from `INFORMATION_SCHEMA`.`TABLES` where (`information_schema`.`TABLES`.`TABLE_SCHEMA` = database())";
      $database_info = $PACMEC['DB']->get_tables_info();
      $tables_ckecks = [
        'categories'         => false,
        'menus'              => false,
        'menus_elements'     => false,
        'notifications'      => false,
        'options'            => false,
        'permissions'        => false,
        'permissions_group'  => false,
        'permissions_items'  => false,
        'permissions_users'  => false,
        'routes'             => false,
        'sessions'           => false,
        'users'              => false,
        //'invisibles'         => false,
      ];
      foreach ($database_info as $slug_gbl => $tbl) {
        if(isset($tables_ckecks[$slug_gbl])) {
          $tables_ckecks[$slug_gbl] = true;
        }
      }
      if(\in_array(false, \array_values($tables_ckecks)) == true) throw new \Exception("Faltan tablas: ".\json_encode($tables_ckecks, JSON_PRETTY_PRINT)."\n", 1);
    } catch (\Exception $e) {
      exit($e->getMessage());
    }
  }

  public static function pacmec_init_options()
  {
    try {
      global $PACMEC;
      $tbl = $PACMEC['DB']->getTableName('options');
      foreach($PACMEC['DB']->FetchAllObject("SELECT * FROM `{$tbl}` WHERE `host` IN (?,?) ORDER BY `host` ASC", ['*', $PACMEC['host']]) as $option){
        $PACMEC['options'][$option->option_name] = Self::pacmec_parse_value($option->option_value);
      }
      $options_ckecks = [
        'siteurl'=>false,
        'sitename'=>false,
        'sitedescr'=>false,
        'sitelogo'=>false,
        'enable_ssl'=>false,
        'charset'=>false,
        'lang_default'=>false,
        'html_type'=>false,
        'site_currency'=>false,
        'homeurl'=>false,
        'footer_by'=>false,
        'plugins_activated'=>false,
        'theme_default'=>false,
        'format_time_s'=>false,
        'format_date_s'=>false,
        'site_format_currency'=>false,
      ];
      foreach ($options_ckecks as $key => $value) { if(Self::get_option_site($key) !== false && !empty($key)) $options_ckecks[$key] = true; }
      if(in_array(false, array_values($options_ckecks)) == true) throw new \Exception("Error en las opciones del sitio.".\json_encode($options_ckecks, JSON_PRETTY_PRINT)."\n", 1);
    } catch (\Exception $e) {
      exit($e->getMessage());
    }
  }

  public static function get_option_site($option_value)
  {
    global $PACMEC;
    if(isset($PACMEC['options'][$option_value])) return $PACMEC['options'][$option_value];
    return false;
  }

  public static function pacmec_parse_value($option_value)
  {
    switch ($option_value) {
      case 'true':
        return true;
        break;
      case 'false':
        return false;
        break;
      case 'null':
        return null;
        break;
      default:
        return $option_value;
        break;
    }
  }

  public static function pacmec_init_route(Array ...$params)
  {
    global $PACMEC;
    /*if(isset($params['request_uri'])){
      $host = isset($params['host']) ? $params['host'] : $PACMEC['host'];
    }*/
    #return $m;
  }

  public static function get_detect_lang()
  {
    global $PACMEC;
    $result = PACMEC_LANG_DEF;
    if(!empty($PACMEC['fullData']['lang'])){
      $result = in_array($PACMEC['fullData']['lang'], \array_keys($GLOBALS['PACMEC']['glossary'])) ? $PACMEC['fullData']['lang'] : (!empty($_COOKIE['language']) ? $_COOKIE['language'] : PACMEC_LANG_DEF);
    } else if (isset($_COOKIE['language']) && !empty($_COOKIE['language']) && isset($GLOBALS['PACMEC']['glossary'][$_COOKIE['language']])){
      $result = $_COOKIE['language'];
    }
    if($result !== $_COOKIE['language']) setcookie('language', $GLOBALS['PACMEC']['lang']);
    return $result;
  }

  public static function get_langs_http() : Array
  {
    global $PACMEC;
    $a = [];
    foreach(\glob(PACMEC_PATH."/i18n/*") as $file_path){
      $file_info = Self::validate_file($file_path);
      if(isset($file_info['translation_for_the_language']) && isset($file_info['version']) && isset($file_info['text_domain'])){
        $slug = $file_info['translation_for_the_language'];
        $text_domain = $file_info['text_domain'];
        if(!isset($a[$slug])) $a[$slug] = [];
        if(!isset($a[$slug]['files'])) $a[$slug]['files'] = [];
        if(!isset($a[$slug]['dictionary'][$text_domain])) $a[$slug]['dictionary'][$text_domain] = [];
        $info_lang = Self::extract_info_lang($file_path);
        $a[$slug]['dictionary'][$text_domain] = $info_lang;
        $a[$slug]['files'][$file_path] = $file_info;
      }
    }
    return $a;
  }

  public static function extract_info_lang($file_path)
  {
    $info_r = [];
    if((!\is_file($file_path) && !\file_exists($file_path)) || \is_dir($file_path) && $is_file($file_path)) return $info_r;
    $texto = @\file_get_contents($file_path);
    $input_line = \nl2br($texto);
    \preg_match_all('/[*\s](.+)[=]+[\s]+([a-zA-Z0-9]+[^<]+)/mi', $input_line, $detect_array);
    foreach($detect_array[1] as $i=>$lab){ $info_r[\str_replace([], [], \strtolower($lab))] = $detect_array[2][$i]; }
    return $info_r;
  }

  public static function validate_file($file_path)
  {
    $info_r = [];
    if((!\is_file($file_path) && !\file_exists($file_path)) || \is_dir($file_path) && $is_file($file_path)) return $info_r;
    $texto = @\file_get_contents($file_path);
    $input_line = \nl2br($texto);
    \preg_match_all('/[*\s]+([a-zA-Z\s\i]+)[:]+[\s]+([a-zA-Z0-9]+[^<]+)/mi', $input_line, $detect_array);
    foreach($detect_array[1] as $i=>$lab){ $info_r[\str_replace(['  ', ' ', '+'], '_', \strtolower($lab))] = $detect_array[2][$i]; }
    return $info_r;
  }

  public static function validate_type_file($file_path)
  {
    if(\is_dir($file_path) && $is_file($file_path)) { return "directory"; }
    else {
  		if(\is_file($file_path) && \file_exists($file_path) && !is_dir($file_path)){
  			$texto = @\file_get_contents($file_path);
  			$input_line = \nl2br($texto);
  			preg_match_all('/[*\s]+([a-zA-Z\s\i]+)[:]+[\s]+([a-zA-Z0-9]+[^<]+)/mi', $input_line, $detect_array);
  			// validar si es traduccion
        $detect = [];
  			foreach($detect_array[1] as $i=>$lab){ $detect[str_replace(['  ', ' ', '+'], '_', strtolower($lab))] = $detect_array[2][$i]; }
  			if(isset($detect['translation_for_the_language']) && isset($detect['version']) && isset($detect['text_domain'])){
  				return "glossary";
  			}
  			// validar si es plugin
        $detect = [];
  			foreach($detect_array[1] as $i=>$lab){ $detect[str_replace(['  ', ' ', '+'], '_', strtolower($lab))] = $detect_array[2][$i]; }
  			if(isset($detect['plugin_name']) && isset($detect['version'])){
  				return "plugin";
  			}
  			// validar si es tema
        $detect = [];
  			foreach($detect_array[1] as $i=>$lab){ $detect[str_replace(['  ', ' ', '+'], '_', strtolower($lab))] = $detect_array[2][$i]; }
  			if(isset($detect['theme_name']) && isset($detect['version'])){
  				return "theme";
  			}
  		}
  	}
  	return "undefined";
  }

  public static function get_current_path()
  {
    $query = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
  	$path = (strtok($query, '?'));
  	$request = \str_replace([PACMEC_HOST], '', $path);
  	$array = explode("/", $request);

    return [
      "query" => $query,
      "path" => $path,
      "request" => $request,
      "array" => $array,
    ];
  }

  /**
  * Ejecutar PACMEC
  *
  * @author FelipheGomez <feliphegomez@gmail.com>
  * @return  : \PACMEC\System\Run
  */
  public static function exec()
  {
    # echo "PACMEC\System\Run::exec()\n";
    return new Self;
  }

  /**
   * IP del cliente
   *
   * @author FelipheGomez <feliphegomez@gmail.com>
   */
  public static function get_ip_address()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      if (\strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
        $iplist = \explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) { if (Self::validate_ip($ip)) return $ip; }
      } else {
        if (Self::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
      }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && Self::validate_ip($_SERVER['HTTP_X_FORWARDED'])) return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && Self::validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && Self::validate_ip($_SERVER['HTTP_FORWARDED_FOR'])) return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && Self::validate_ip($_SERVER['HTTP_FORWARDED'])) return $_SERVER['HTTP_FORWARDED'];
    return $_SERVER['REMOTE_ADDR'];
  }

  /**
   * Garantiza que una dirección IP sea una IP válida y no se encuentre dentro del rango de una red privada.
   */
  public static function validate_ip($ip) {
    if (\strtolower($ip) === 'unknown') return false;
    $ip = \ip2long($ip);
    if ($ip !== false && $ip !== -1) {
      $ip = \sprintf('%u', $ip);
      if ($ip >= 0 && $ip <= 50331647) return false;
      if ($ip >= 167772160 && $ip <= 184549375) return false;
      if ($ip >= 2130706432 && $ip <= 2147483647) return false;
      if ($ip >= 2851995648 && $ip <= 2852061183) return false;
      if ($ip >= 2886729728 && $ip <= 2887778303) return false;
      if ($ip >= 3221225984 && $ip <= 3221226239) return false;
      if ($ip >= 3232235520 && $ip <= 3232301055) return false;
      if ($ip >= 4294967040) return false;
    }
    return true;
  }

  /**
  * Sanear datos JSON e INPUTs
  *
  * @author FelipheGomez <feliphegomez@gmail.com>
  * @return  : Array
  */
  public static function post_data_json() : Array
  {
    try {
      $r  =  [];
      $rawData = @\file_get_contents("php://input");
      if(@\json_decode($rawData) !== null) {
        foreach (@\json_decode($rawData) as $k => $v) {
          $r[$k] = $v;
        }
      };
      return $r;
    } catch (\Exception $e) {
      return [];
    }
  }

  /**
   * Retornar informacion recibida
   *
   * @author FelipheGomez <feliphegomez@gmail.com>
   * @return Array
   */
  public static function get_data_full() : Array
  {
    try {
      $json = Self::post_data_json();
      $a = @\array_merge($_GET, $_POST, $json);
      return \is_array($a) ? $a : [];
    } catch (\Exception $e) {
      return [];
    }
  }
}
