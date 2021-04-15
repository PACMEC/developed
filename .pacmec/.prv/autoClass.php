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
   /*
   $file = !is_file($ruta_1) ? !is_file($ruta_2) ? !is_file($ruta_3) ? "" : $ruta_3 : $ruta_2 : $ruta_1;

   if($file != ""){
     require_once($file);
   }else{
     echo "Clase no existe. " . $file . "\n";
     exit();
   }*/
   exit();
 }
}

spl_autoload_register('pacmec_load_classes');
