<?php
/**
 * Plugin Name: Hello PACMEC
 * Plugin URI: https://managertechnology.com.co/
 * Description: El complemento de muestra para devs PACMEC
 * Version: 0.1
 * Author: FelipheGomez
 * Author URI: https://github.com/FelipheGomez/PACMEC-Hello
 * Text Domain: pacmec-hello
 * Copyright 2020-2021
 * (email : feliphegomez@gmail.com)
 * GPLv2 Full license details in license.txt
 */

# echo "plugin: Hello incluido";

function pacmec_Hello_PACMEC_activation()
{
 try {
   $tbls = [];
   foreach ($tbls as $tbl) {
     if(!pacmec_tbl_exist($tbl)){
       throw new \Exception("Falta la tbl: {$tbl}", 1);
     }
   }
   echo "plugin: Hello activado";
 } catch (\Exception $e) {
   echo $e->getMessage();
   exit;
 }
}
\register_activation_plugin('pacmec-hello', 'pacmec_Hello_PACMEC_activation');
