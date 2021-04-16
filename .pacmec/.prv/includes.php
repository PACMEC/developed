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
namespace PACMEC;
class Autoload
{
  public function __construct()
  {
    if(!isset($GLOBALS['PACMEC'])) {
      global $PACMEC;
        $PACMEC['autoload'] = [
          "classes"     => [],
          "dictionary"     => [],
        ];
        $PACMEC['hooks'] = null;
        $PACMEC['DB'] = null;
        $PACMEC['ip'] = null;
        $PACMEC['host'] = null;
        $PACMEC['fullData'] = null;
        $PACMEC['lang'] = null;
        $PACMEC['path_orig'] = null;
        $PACMEC['path'] = null;
        $PACMEC['glossary'] = null;
        $PACMEC['website'] = [
          "meta" => [],
          "scripts" => ["head"=>[],"foot"=>[],"list"=>[]],
          "styles" => ["head"=>[],"foot"=>[],"list"=>[]]
        ];
        $PACMEC['session'] = null;
        $PACMEC['theme'] = [];
        $PACMEC['themes'] = [];
        $PACMEC['plugins'] = [];
        $PACMEC['options'] = [];
        $PACMEC['alerts'] = [];
        $PACMEC['total_records'] = [];
        $PACMEC['route'] = null;
    }
  }

  public function autoload($class)
  {
    try {
      global $PACMEC;
      if (\class_exists($class)) {
        echo " - Existe $class\n";
        //return new $s();
        exit;
      }

      $class_r = str_replace("\\","/", $class);
      $class_r = str_replace("PACMEC","", $class_r);
      $namespace = str_replace("\\","/", __NAMESPACE__);
      $ruta_a = PACMEC_PATH . "/{$class_r}.php";
      $ruta_b = PACMEC_PATH . "/{$class_r}.php";
      $ruta_c = PACMEC_PATH . "/libs/" . "{$class}.php";
      $ruta_d = PACMEC_PATH . "/libs/" . (empty($namespace) ? "" : $namespace . "/") . "{$class}.php";
      $file = null;

      if (\is_file($ruta_a) && \file_exists($ruta_a)){
        $file = ($ruta_a);
      } elseif (\is_file($ruta_b) && \file_exists($ruta_b)){
        $file = ($ruta_b);
      } elseif (\is_file($ruta_c) && \file_exists($ruta_c)){
        $file = ($ruta_c);
      } elseif (\is_file($ruta_d) && \file_exists($ruta_d)){
        $file = ($ruta_d);
      } else {
          throw new \Exception("Archivo no encontrado. {$class}... ");
      }
      require_once $file;

      if (!\class_exists($class)) {
        echo ("Class no encontrada. {$class}... ");
      } else {
        $PACMEC['autoload']['classes'][$class] = $file;
      }
    } catch (\Exception $e) {
      echo "pClassName: {$class}\n";
      echo ("PACMEC-ERROR: Autoload::autoload() - {$e->getMessage()}\n");
      echo json_encode($e->getTrace(), JSON_PRETTY_PRINT)."\n";


      echo "class         : " . $class;
      echo "\n";
      echo "__NAMESPACE__ : " . __NAMESPACE__;
      echo "\n";
      echo "class r       : " . $class_r;
      echo "\n";
      echo "namespace     : " . $namespace;
      echo "\n";
      echo "ruta_a        : " . $ruta_a;
      echo "\n";
      echo "ruta_b        : " . $ruta_b;
      echo "\n";
      echo "ruta_c        : " . $ruta_c;
      echo "\n";
      echo "ruta_d        : " . $ruta_d;
      echo "\n";

      exit();
    }
  }
}
\spl_autoload_register(array(new \PACMEC\Autoload(), 'autoload'));


/*

function pacmec_load_classes($class)
{
 try {
   $class = str_replace("\\","/", $class);
   $namespace = str_replace("\\","/", __NAMESPACE__);
   $ruta_a = PACMEC_PATH . "includes/classes/{$class}.php";
   $ruta_b = PACMEC_PATH . "includes/classes/" . (empty($namespace) ? "" : $namespace . "/") . "{$class}.php";

   if (is_file($ruta_a) && file_exists($ruta_a))
   {
     require_once($ruta_a);
   }
   else
   {
     if(@get_called_class() != false){
       if (is_file($ruta_b) && file_exists($ruta_b))
       {
         require_once($ruta_b);
       }
       else
       {
         echo "class              : {$class}" . "\n";
         echo "namespace          : {$namespace}" . "\n";
         echo "parent Class       : " . json_encode(@get_called_class()) . "\n";
         echo "ruta_a      : {$ruta_a}" . "\n";
         echo "ruta_a    : " . json_encode(file_exists($ruta_a)) . "\n";
         echo "ruta_b      : {$ruta_b}" . "\n";
         echo "ruta_b    : " . json_encode(file_exists($ruta_b)) . "\n";
         throw new Exception("Archivo no encontrado. {$class}");
       }
     }
   }
 }
 catch(Exception $e) {
   echo ($e->getMessage());
   echo "\n";
   echo json_encode($e->getTrace(), JSON_PRETTY_PRINT);
   exit();
 }
}

spl_autoload_register('pacmec_load_classes');

*/
