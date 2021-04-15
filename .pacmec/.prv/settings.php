<?php
/* *******************************
 *
 * Developer by FelipheGomez
 *
 * ******************************/
/** Define PACMEC_PATH as this file's directory */
if (!defined('SITE_PATH')) define('SITE_PATH', dirname(PACMEC_PATH) . '/');
if (!defined('PACMEC_PATH')) define('PACMEC_PATH', __DIR__ . '/');

// DATABASE CONFIG
define('DB_port', '3306');               // Base de datos: Puerto de conexion (Def: 3306)
define('DB_driver', 'mysql');            // Base de datos: Controlador de la conexion; (Def: mysql)
define('DB_host', 'localhost');          // Base de datos: Servidor/Host de conexion
define('DB_user', 'pacmec_u');           // Base de datos: Usuario de conexion
define('DB_pass', 'pacmec_p');           // Base de datos: Contraseña del usuario
define('DB_database', 'pacmec_db');      // Base de datos: Nombre de la base de datos
define('DB_charset', 'utf8mb4');         // Base de datos: Caracteres def
define('DB_prefix', 'mt_');              // Base de datos: Prefijo de las tablas (Opcional)

define('AUTH_KEY_COST', 10);             // Nivel de encr: Costo del algoritmo
define('MODE_DEBUG', false);             // Modo    Debug: Activar el modo DEBUG

define("SMTP_HOST", "server-smtp");      // SMTP   Config: Servidor/Host SMTP
define("SMTP_AUTH", true);               // SMTP   Config: Activar autenticacion
define("SMTP_USER", "user-smtp");        // SMTP   Config: Usuario SMTP
define("SMTP_PASS", "pass-smtp");        // SMTP   Config: Contraseña del usuario
define("SMTP_SECURE", 'ssl');            // SMTP   Config: Protocolo de conexion ssl/tls
define("SMTP_PORT", "465");              // SMTP   Config: Puerto de conexion
define("SMTP_CC", false);                // SMTP   Config: Correo para CC en correos salientes desde el correo PACMEC.
define("SMTP_BCC", false);               // SMTP   Config: Ccorreo para BCC en correos salientes desde el correo PACMEC.

define('PACMEC_HOST', 'myserver');       // PACMEC Config: Host Actual (Para evitar suplentacion)
