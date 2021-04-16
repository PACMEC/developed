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
    require_once PACMEC_PATH . '/functions.php';
    Self::pacmec_create_globals_vars();
    Self::pacmec_init_setup();
    Self::pacmec_init_options();
    Self::pacmec_run_session();
    Self::pacmec_init_session();
    Self::pacmec_init_files_includes();
    Self::pacmec_init_plugins_actives();
    Self::pacmec_init_themes();
    Self::pacmec_init_route();
    Self::pacmec_validate_route();
    Self::pacmec_theme_check();
    Self::pacmec_assets_globals();
    Self::pacmec_run_ui();
  }

  public function pacmec_run_session()
  {
    \session_set_save_handler(new \PACMEC\System\SysSession(), true);
    if(Self::is_session_started() === FALSE || \session_id() === "") \session_start();
  }

  public function pacmec_init_session()
  {
    global $PACMEC;
    //$_SESSION['user']['id'] = 1;
    $PACMEC['session'] = new \PACMEC\System\Session();
  }

  public static function pacmec_theme_check()
  {
    // VALIDAR QUE ESTE ACTIVO EL TEMA
    if(isset($GLOBALS['PACMEC']['theme']['active']) && $GLOBALS['PACMEC']['theme']['active'] == true){
    } else {
      exit("El tema no existe.");
    }
  }

  public static function pacmec_assets_globals()
  {
    // add_style_head(siteinfo('siteurl')   . "/.pacmec/assets/css/pacmec.css"."?&cache=".rand(),  ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 1, false);
    // add_style_head(siteinfo('siteurl')   . "/.pacmec/assets/css/plugins.css", ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], 0.99, false);
    // add_scripts_head(siteinfo('siteurl') . "/.pacmec/assets/js/plugins.js",   ["type"=>"text/javascript", "charset"=>"UTF-8"], 1, false);
    // add_scripts_head(siteinfo('siteurl') . "/.pacmec/assets/dist/sweetalert2/sweetalert2.all.min.js",    ["type"=>"text/javascript", "charset"=>"UTF-8"], 0, false);
    // add_scripts_head(siteinfo('siteurl') . "/.pacmec/assets/dist/vue/vue.min.js",    ["type"=>"text/javascript", "charset"=>"UTF-8"], 0, false);
    // add_scripts_head(siteinfo('siteurl') . "/.pacmec/assets/dist/vue/vue-router.js",    ["type"=>"text/javascript", "charset"=>"UTF-8"], 0, false);
    // add_scripts_head(siteinfo('siteurl') . "/.pacmec/assets/js/sdk.js"."?&cache=".rand(),   ["type"=>"text/javascript", "charset"=>"UTF-8"], 0, false);
  }

  public static function pacmec_run_ui()
  {
    if(
      isset($GLOBALS['PACMEC']['theme']['path'])
      && is_file($GLOBALS['PACMEC']['theme']['path'] . '/index.php')
      && file_exists($GLOBALS['PACMEC']['theme']['path'] . '/index.php')
    )
    {
      $data = $GLOBALS['PACMEC']['fullData'];
      if(isset($data['controller']))
      {
        $controllerObj = cargarControlador($data["controller"]);
        lanzarAccion($controllerObj);
      }
      else
      {
        require_once $GLOBALS['PACMEC']['theme']['path'] . '/index.php';
      }
    } else {
      throw new \Exception("Hubo un problema al ejecutar la Interfas de Usuario. {$GLOBALS['PACMEC']['theme']['text_domain']} -> index.php]", 1);
      ;
      exit;
    }
  }

  public static function pacmec_validate_route()
  {
    //echo "pacmec_validate_route";
    if($GLOBALS['PACMEC']['route']->theme == null) $GLOBALS['PACMEC']['route']->theme = siteinfo('theme_default');
    add_action('page_title', function(){ if(isset($GLOBALS['PACMEC']['route']->id)){ echo (pageinfo('page_title') !== 'NaN') ? _autoT(pageinfo('page_title')) : _autoT(pageinfo('title')); } });
    add_action('page_description', function(){ if(isset($GLOBALS['PACMEC']['route']->id)){ echo (pageinfo('page_description') !== 'NaN') ? pageinfo('page_description') : _autoT(pageinfo('description')); } });
    add_action('page_body', function(){
    	if(isset($GLOBALS['PACMEC']['route']->request_uri) && $GLOBALS['PACMEC']['route']->request_uri !== ""){
    		$GLOBALS['PACMEC']['route']->content = do_shortcode($GLOBALS['PACMEC']['route']->content);
        echo $GLOBALS['PACMEC']['route']->content;
    	}
    	else {
    		echo do_shortcode(
    			errorHtml("Lo sentimos, no se encontro el archivo o página.", "Ruta no encontrada")
    		);
    	}
    });
    /*
    add_action('head', function(){
      $r = "\t<script type=\"text/javascript\" charset=\"UTF-8\">";
      $r .= "
        window.mtAsyncInit = function() {
          PACMEC.init({
        		api_server : 'https://pacmec.managertechnology.com.co',
        		appId      : 'managertechnology',
        		token      : 'pacmec',
        		Wmode      : '".infosite('wco_mode')."',
        		Wversion      : 'v1',
        	});
        };";
      $r .= "
      </script>";
      echo $r;
      //Wmode      : '<?= WCO_MODE; ?',
    });*/
  }

  public static function pacmec_init_themes()
  {
    global $PACMEC;
    $path_theme = null;
    $path = PACMEC_PATH."/themes";
    $theme_def = \siteinfo('theme_default');

    if(is_dir("{$path}/{$theme_def}")){
      $path_theme = "{$path}/{$theme_def}/{$theme_def}.php";
    } else if(is_file("{$path}/{$theme_def}.php")){
      $path_theme = "{$path}/{$theme_def}.php";
    }

    if(is_file($path_theme)){
      $file_info = Self::validate_file($path_theme);
      if(isset($file_info['theme_name'])){
        $PACMEC['themes'][$file_info['text_domain']] = $file_info;
        $PACMEC['themes'][$file_info['text_domain']]['active'] = false;
        $PACMEC['themes'][$file_info['text_domain']]['path'] = dirname($path_theme);
        $PACMEC['themes'][$file_info['text_domain']]['file'] = ($path_theme);
        if(is_file($PACMEC['themes'][$file_info['text_domain']]['file'])){
    			//require_once $path_theme;
          //\activation_plugin($file_info['text_domain']);
    		}
      }
      foreach(\glob($path."/*/*") as $file_path){
        $dirname = dirname($file_path);
        $name = str_replace(['.php'], [''], basename($file_path));
        $file_info = Self::validate_file($file_path);
        if(isset($file_info['theme_name'])){
          $PACMEC['themes'][$file_info['text_domain']] = $file_info;
          $PACMEC['themes'][$file_info['text_domain']]['active'] = false;
          $PACMEC['themes'][$file_info['text_domain']]['path'] = $dirname;
          $PACMEC['themes'][$file_info['text_domain']]['file'] = ($file_path);
        }
      }
      foreach(\glob($path."/*.php") as $file_path){
        $dirname = dirname($file_path);
        //$name = str_replace(['.php'], [''], basename($file_path));
        $file_info = Self::validate_file($file_path);
        if(isset($file_info['theme_name'])){
          $PACMEC['themes'][$file_info['text_domain']] = $file_info;
          $PACMEC['themes'][$file_info['text_domain']]['active'] = false;
          $PACMEC['themes'][$file_info['text_domain']]['path'] = $dirname;
          $PACMEC['themes'][$file_info['text_domain']]['file'] = ($file_path);
        }
      }
    }
    else {
      /*\PACMEC\System\Alert::addAlert([
        "type"        => "error",
        "plugin"      => "system",
        "message"     => "El tema {$path_theme}, no tiene el formato correcto.\n",
        "actions"  => [
          [
            "name" => "themes-errors",
            "theme" => $path_theme,
            "slug" => "/?c=admin&a=themes&p={$path_theme}&tab=errors_logs",
            "text" => "Ups error"
          ]
        ],
      ]);*/
      throw new \Exception("No existe el tema principal [{$theme_def}]. path: {$path_theme}", 1);
      exit();
    }
  }

  public static function pacmec_init_plugins_actives()
  {
    global $PACMEC;
    $path = PACMEC_PATH."/plugins";
    $plugins_activateds = explode(',', \siteinfo('plugins_activated'));
    foreach($plugins_activateds as $p){
      $path_plugin = null;
      if(is_dir("{$path}/{$p}")){
        $path_plugin = "{$path}/{$p}/{$p}.php";
      } else if(is_file("{$path}/{$p}.php")){
        $path_plugin = "{$path}/{$p}.php";
      }
      if(is_file($path_plugin)){
        $file_info = Self::validate_file($path_plugin);
        if(isset($file_info['plugin_name'])){
          $PACMEC['plugins'][$file_info['text_domain']] = $file_info;
          $PACMEC['plugins'][$file_info['text_domain']]['active'] = true;
          $PACMEC['plugins'][$file_info['text_domain']]['path'] = dirname($path_plugin);
          $PACMEC['plugins'][$file_info['text_domain']]['file'] = ($path_plugin);
          if(is_file($PACMEC['plugins'][$file_info['text_domain']]['file'])){
      			require_once $path_plugin;
            \activation_plugin($file_info['text_domain']);
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
      require_once PACMEC_PATH . '/actions.php';
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
      $PACMEC['theme'] = [];
      $PACMEC['plugins'] = [];
      $PACMEC['options'] = [];
      $PACMEC['alerts'] = [];
      $PACMEC['total_records'] = $PACMEC['DB']->getTotalRows();
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
        //'author'             => false,
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
        'author'=>false,
        //'site_format_currency'=>false,
      ];
      foreach ($options_ckecks as $key => $value) { if(Self::get_option_site($key) !== false && !empty($key)) $options_ckecks[$key] = true; }
      if(in_array(false, array_values($options_ckecks)) == true) throw new \Exception("Error en las opciones del sitio.".\json_encode($options_ckecks, JSON_PRETTY_PRINT)."\n", 1);

      setlocale(LC_ALL, $PACMEC['lang']); /* Establecer el localismo */
      // setlocale(LC_MONETARY, \infosite('site_format_currency')); /* Establecer el localismo */
      if(Self::get_option_site($key) == true && $_SERVER["HTTPS"] != "on") { header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]); exit(); }
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

  public static function pacmec_init_route()
  {
    try {
      global $PACMEC;
      $PACMEC['route'] = new \PACMEC\System\Route();
      switch ($PACMEC['path']) {
        case '/pacmec-api':
        case '/pacmec-api-doc':
          throw new \Exception("No implementado para esta version.", 1);
          break;
        default:
          // throw new \Exception("Ruta no encontrada.", 1);
          //$PACMEC['route']->"Ruta no encontrada.";
          $PACMEC['route']->getBy('request_uri', $PACMEC['path']);
          break;
      }

      $model_route = $PACMEC['route'];
      if($model_route->id>0&&$model_route->is_actived==true){
        //
        pacmec_add_meta_tag('title', _autoT($GLOBALS['PACMEC']['route']->title));
        pacmec_add_meta_tag('description', _autoT($GLOBALS['PACMEC']['route']->description));

      } else {
        pacmec_add_meta_tag('title', infosite('sitename'));
        pacmec_add_meta_tag('description', infosite('sitedescr'));
      }
      pacmec_add_meta_tag('site_name', infosite('sitename'));
      pacmec_add_meta_tag('og:email', _autoT('about_email'));
      pacmec_add_meta_tag('og:phone_number', _autoT('about_sales'));
      pacmec_add_meta_tag('language', $GLOBALS['PACMEC']['lang']);
      pacmec_add_meta_tag('url', infosite('siteurl').$GLOBALS['PACMEC']['path']);
      pacmec_add_meta_tag('favicon', infosite('sitefavicon'));
      pacmec_add_meta_tag('generator', 'PACMEC 1.0.1');

      if(pacmec_exist_meta('og:image')==false) pacmec_add_meta_tag('image', infosite('sitelogo'));
      if(pacmec_exist_meta('robots')==false && infosite('robots')!=="NaN") { pacmec_add_meta_tag('robots', infosite('siterobots')); } else { pacmec_add_meta_tag('robots', 'index,follow'); };
      //if(pacmec_exist_meta('Classification') == false && infosite('Classification')!=="NaN") { pacmec_add_meta_tag('Classification', infosite('Classification')); } else { pacmec_add_meta_tag('Classification', 'Internet'); };
      if(pacmec_exist_meta('author')==false && infosite('author')!=="NaN") { pacmec_add_meta_tag('author', infosite('author')); } else { pacmec_add_meta_tag('author', DevBy()); };
      if(pacmec_exist_meta('og:type')==false) pacmec_add_meta_tag('og:type', 'Website');


      # echo json_encode($PACMEC['route'], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
      exit("{$e->getMessage()}\n");
    }

    /*
       else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-api-docs'){
        require_once 'api-doc.php';
        exit;
      } else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-close-session'){
        $redirect = infosite("siteurl").infosite("homeurl");
        $GLOBALS['PACMEC']['session']->close();
        header("Location: ".$redirect);
        echo "<meta http-equiv=\"refresh\" content=\"0; url={$redirect}\">";
        exit;
      } else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-form-sign'){
        $model_route = new PACMEC\ROUTE();
        $model_route->theme = 'system';
        $model_route->component = 'pages-signin';
        $model_route->title = _autoT('signin');
        $args = dataFull();
      } else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-recover-password'){
        $model_route = new PACMEC\ROUTE();
        $model_route->theme = 'system';
        $model_route->component = 'pages-recover-password';
        $model_route->title = _autoT('recover_password');
        $args = dataFull();
      } else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-form-contact'){
        header('Content-Type: application/json');
        $merge = dataFull();
        $args = array_merge(['args'=>$merge], $merge);
        get_part('components/contact-form-backend', PACMEC_CRM_COMPONENTS_PATH, $args);
        exit;
      } else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-staff'){
        $model_route = new PACMEC\ROUTE();
        $model_route->theme = 'system';
        $model_route->component = 'pages-staff';
        $model_route->title = _autoT('pagestaff');
        //$args = dataFull();
      } else if(isset($detectAPI[1]) && $detectAPI[1] == 'pacmec-form-contact-services'){
        header('Content-Type: application/json');
        $merge = dataFull();
        $args = array_merge(['args'=>$merge], $merge);
        get_part('components/services-contact-form-backend', PACMEC_PATH.'/system/', $args);
        exit;
      } else if(isset($detectAPI[1])) {
        /*switch ($detectAPI[1]) {
          case _autoT('%autot_services%'):
            $detectAPI[1] = '%autot_services%';
            break;
          default:
            break;
        }* /
      }
    	$GLOBALS['PACMEC']['req_url'] = $reqUrl;
      if(!isset($model_route))
      {
        $model_route = new PACMEC\ROUTE([
          'page_slug'=>$reqUrl
        ]);
      }
      // Comprobar que exista y que esté activo.
      $GLOBALS['PACMEC']['route'] = $model_route;

    */
    #return $m;
  }

  public static function get_detect_lang()
  {
    global $PACMEC;
    $result = PACMEC_LANG_DEF;
    if(!empty($PACMEC['fullData']['lang'])){
      $result = in_array($PACMEC['fullData']['lang'], \array_keys($PACMEC['glossary'])) ? $PACMEC['fullData']['lang'] : (!empty($_COOKIE['language']) ? $_COOKIE['language'] : PACMEC_LANG_DEF);
    } else if (isset($_COOKIE['language']) && !empty($_COOKIE['language']) && isset($PACMEC['glossary'][$_COOKIE['language']])){
      $result = $_COOKIE['language'];
    }
    if(!isset($_COOKIE['language']) || $result !== $_COOKIE['language']) setcookie('language', $PACMEC['lang']);
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
        //if(!isset($a[$slug]['files'])) $a[$slug]['files'] = [];
        if(!isset($a[$slug][$text_domain])) $a[$slug][$text_domain] = [];
        $info_lang = Self::extract_info_lang($file_path);
        $a[$slug][$text_domain] = $info_lang;
        $PACMEC['autoload']['dictionary'][$file_path] = $file_info;
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
