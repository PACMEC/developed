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
        $PACMEC['autoload'] = [];
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
        $PACMEC['plugins'] = [];
        $PACMEC['options'] = [];
        $PACMEC['alerts'] = [];
        $PACMEC['total_records'] = [];
        $PACMEC['route'] = null;
    }
  }

  public function autoload($pClassName)
  {
    try {
      global $PACMEC;
      $nameclass = \str_replace(['\\', '//', '/', "\/"], PACMEC_DEF_SEPARATOR_PATH, $pClassName);
      $namespace = \str_replace(['\\', '//', '/', "\/"], PACMEC_DEF_SEPARATOR_PATH, __NAMESPACE__);
      if(!empty($namespace)) $nameclass = \str_replace($namespace, '', $nameclass);
      if (\class_exists($pClassName)) {
        echo " - Existe $pClassName O $nameclass\n";
        //return new $s();
      } else {
        switch ($namespace) {
          case 'PACMEC':
            $file = PACMEC_PATH."/{$nameclass}.php";
            if(\is_file($file) && \file_exists($file)){
              require_once($file);
              if (!\class_exists($pClassName)) {
                throw new \Exception("Class no encontrada. {$file}... {$namespace}::{$nameclass}");
              } else {
                $PACMEC['autoload'][$pClassName] = $file;
              }
            } else {
              throw new \Exception("Archivo no encontrado. {$file}... {$namespace} :: {$nameclass}");
            }
            break;
          default:
            throw new \Exception("Class no encontrada. {$file}... {$namespace}::{$nameclass}");
            break;
        }
      }
    } catch (\Exception $e) {
      echo "pClassName: {$pClassName} - namespace: {$namespace} - nameclass: {$nameclass}\n";
      echo ("PACMEC-ERROR: Autoload::autoload() - {$e->getMessage()}\n");
      echo json_encode($e->getTrace(), JSON_PRETTY_PRINT);
      exit();
    }
  }
}
\spl_autoload_register(array(new \PACMEC\Autoload(), 'autoload'));
