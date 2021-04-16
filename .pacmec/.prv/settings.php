<?php
/**
 *
 * @package    PACMEC
 * @category   Settings
 * @version    0.0.1
 */
define('DB_port', '3306');                                   // Base de datos: Puerto de conexion (Def: 3306)
define('DB_driver', 'mysql');                                // Base de datos: Controlador de la conexion (Def: mysql)
define('DB_host', 'localhost');                              // Base de datos: Servidor/Host de conexion (Def: localhost)
define('DB_user', 'pacmec_u');                               // Base de datos: Usuario de conexion
define('DB_pass', 'pacmec_p');                               // Base de datos: Contraseña del usuario
define('DB_database', 'pacmec_dev');                         // Base de datos: Nombre de la base de datos
define('DB_charset', 'utf8mb4');                             // Base de datos: Caracteres def
define('DB_prefix', 'mt_');                                  // Base de datos: Prefijo de las tablas (Opcional)

define('AUTH_KEY_COST', 10);                                 // Nivel de encr: Costo del algoritmo
define('MODE_DEBUG', true);                                  // Modo    Debug: Activar el modo DEBUG

define("SMTP_HOST", "localhost");                            // SMTP   Config: Servidor/Host SMTP
define("SMTP_AUTH", true);                                   // SMTP   Config: Activar autenticacion
define("SMTP_USER", "no-reply@localhost");                                    // SMTP   Config: Usuario SMTP
define("SMTP_PASS", "smtp_p");                               // SMTP   Config: Contraseña del usuario
define("SMTP_SECURE", 'ssl');                                // SMTP   Config: Protocolo de conexion ssl/tls
define("SMTP_PORT", "465");                                  // SMTP   Config: Puerto de conexion
define("SMTP_CC", false);                                    // SMTP   Config: Correo para CC en correos salientes desde el correo PACMEC.
define("SMTP_BCC", false);                                   // SMTP   Config: Ccorreo para BCC en correos salientes desde el correo PACMEC.

define('PACMEC_HOST', 'localhost');       // PACMEC Config: Host Actual (Para evitar suplentacion)
define('PACMEC_SSL', true);               // Habilitar SSL Forzado


define('PACMEC_DEF_SEPARATOR_PATH', '/');
define('PACMEC_LANG_DEF', 'es-CO');
