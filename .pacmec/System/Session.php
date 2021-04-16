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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

namespace PACMEC\System;

class Session
{
 	#public  $isGuest          = true;
 	public  $user               = null;
 	public  $permission_group   = null;
 	public  $permissions_items  = [];
 	public  $permissions        = [];
 	public  $notifications      = [];

 	/**
 	* Inicializa la sesión
 	*/
 	public function __construct()
 	{
 		$this->user             = new \stdClass();
 		$this->permission_group = new \stdClass();
 		$this->refreshSession();
 	}

 	public function add_alert(string $message, string $title=null, string $url=null, int $time=null, string $uniqid=null, string $icon=null)
 	{
 		$time = $time==null ? time() : $time;
 		$uniqid = $uniqid==null ? uniqid() : $uniqid;
 		$icon = $icon==null ? "fas fa-bell" : $icon;
 		$url = $url==null ? "#" : $url;
 		$title = $title==null ? "Nueva notificacion" : $title;
 		$date = date('Y-m-d H:i:s', $time);

 		$alert = [
 			"title"=>$title,
 			"message"=>$message,
 			"time"=>$time,
 			"uniqid"=>$uniqid,
 			"date"=>$date,
 			"url"=>$url,
 			"icon"=>$icon,
 		];

 		if(!isset($this->notifications[$uniqid])){
 			$this->set($uniqid, $alert, 'notifications');
 			// $this->notifications[$uniqid] = $_SESSION['notifications'][$uniqid] = $alert;
 		};
 	}

 	public function add_permission(string $tag, $obj=null):bool
 	{
 		$tag = strtolower($tag);
 		if($obj !== null){
 			$obj = (object) $obj;
 		} else {
 			$obj = (object) [
 				"id"=>999999999999999999999999,
 				"tag"=>$tag,
 				"name"=>$tag,
 				"description"=>$tag,
 			];
 		}
 		if(!isset($this->permissions_items[$tag])){
 			$this->permissions_items[$tag] = $_SESSION['permissions_items'][$tag] = $obj;
 		}
 		if(isset($_SESSION['permissions'])&&!in_array($tag, $_SESSION['permissions'])) $this->permissions[] = $_SESSION['permissions'][] = $tag;
 		return true;
 	}

 	public function set($k, $v, $l=null)
 	{
 		if($l == null){
 			$this->{$k} = $_SESSION[$k] = $v;
 		} else {
 			if(is_array($this->{$l})){
 				$this->{$l}[$k] = $_SESSION[$l][$k] = $v;
 			} else {
 				$this->{$l}->{$k} = $_SESSION[$l][$k] = $v;
 			}
 		}
 	}

 	public function refreshSession()
 	{
 		if(\isUser()){
 			try {
        $this->getById(\userID());
 			}
 			catch(Exception $e){
 				echo $e->getMessage();
 				exit();
 			}
 		}
 	}

  public function getById($user_id)
  {
    $tbl = $GLOBALS['PACMEC']['DB']->getTableName('users');
    $dataUser = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$tbl}` WHERE `id`=? ", [ $user_id ]);
    $this->setAll($dataUser);
    return $this;
  }

 	public function setAll($user = [])
 	{
 		$user = (array) $user;
 		foreach($user as $a => $b){ $this->user->{$a} = $b; }
    if(isset($this->user->permissions) && $this->user->permissions !== null && $this->user->permissions > 0 && count($this->permissions)==0){
      $result = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT E.*
        FROM `{$GLOBALS['PACMEC']['DB']->getTableName('permissions')}` D
        JOIN `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_items')}` E
        ON E.`id` = D.`permission`
        WHERE D.`group` IN (?)", [$this->user->permissions]);
      if($result !== false && count($result) > 0){
        foreach($result as $perm){
          $this->add_permission($perm->tag, $perm);
        }
      }
      $result = $GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_group')}` WHERE `id` IN (?)", [$this->user->permissions]);
      if($result !== false){
        $this->permission_group = $result;
      }
    }
    $result = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT E.*
 			FROM `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_users')}` D
 			JOIN `{$GLOBALS['PACMEC']['DB']->getTableName('permissions_items')}` E
 			ON E.`id` = D.`permission`
 			WHERE D.`user_id` IN (?)", [$this->user->id]);
 		if($result !== false && count($result) > 0){
 			foreach($result as $perm){
 				$this->add_permission($perm->tag, $perm);
 			}
 		}
    $this->permissions = array_keys($this->permissions_items);
    $this->notifications = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('notifications')}` WHERE `user_id` IN (?) AND `is_read` IN (?)", [$this->user->id, 0]);
 		foreach ($this as $k => $v) {
 			$_SESSION[$k] = is_object($v) ? (Array) $v : $v;
 		}
 	}

 	/**
 	* Retorna todos los valores del array de sesión
 	* @return el array de sesión completo
 	*/
 	public function getAll()
 	{
 		#$this->refreshSession();
 		return isset($_SESSION['user']) ? $this : [];
 	}

 	/**
 	* Cierra la sesión eliminando los valores
 	*/
 	public static function close()
 	{
 		\session_unset();
 		\session_destroy();
 	}

 	/**
 	* Retorna el estatus de la sesión
 	* @return string el estatus de la sesión
 	*/
 	public static function getStatus()
 	{
 		switch(\session_status())
 		{
 			case 0:
 				return "DISABLED";
 				break;
 			case 1:
 				return "NONE";
 				break;
 			case 2:
 				return "ACTIVE";
 				break;
 		}
 	}

 	/**
 	* Retorna string default
 	* @return string
 	*/
 	public function __toString()
 	{
    // COLOCAL LABEL O GUEST
 		return json_encode($this->getAll());
 	}

 	/**
 	* Retorna array default
 	* @return string
 	*/
 	public function __sleep()
 	{
 		return array_keys($this->getAll());
 	}

 	public function getId()
 	{
 		return !isset($_SESSION['user']['id']) ? 0 : $_SESSION['user']['id'];
 	}

 	public function login($args = [])
 	{
 		$args = (object) $args;
 		if(isset($args->nick) && isset($args->hash)){
 			$result = $this->validateUserDB($args->nick);
 			switch($result){
 				case "error":
 				case "no_exist":
 				case "inactive":
 					return $result;
 					break;
 				case $result->id > 0:
 					if (\password_verify($args->hash, $result->hash) == true) {
 						if (!\headers_sent()) {
 			          \session_regenerate_id(true);
 			      }
 						$this->setAll(['user'=>$result]);
 						return "success";
 					} else {
 						return "invalid_credentials";
 					}
 					break;
 				default:
 					return "error";
 					break;
 			}
 		}
 	}

 	public function validateUserDB($nick_or_email='')
 	{
 		try {
 			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `username`=? AND `status` IN (1) ";
 			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `username`=? ";
 			$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$nick_or_email]);
 			if($result == false){
 				$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `email`=? AND `status` IN (1) ";
 				$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `email`=? ";
 				$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$nick_or_email]);
 			}
 			if($result !== false && isset($result->id)){
 				if($result->status == 0){
 					return "inactive";
 				}
 				return $result;
 			}
 			return "no_exist";
 		}
 		catch(Exception $e){
 			#echo $e->getMessage();
 			return "error";
 		}
 	}

 	public function validateUserDB_recover($key,$email)
 	{
 		try {
 			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `keyrecov` IN (?) AND `email` IN (?) ";
 			$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$key,$email]);
 			if($result !== false && isset($result->id)){
 				if($result->status == 0){
 					return "inactive";
 				}
 				return $result;
 			}
 			return "no_exist";
 		}
 		catch(Exception $e){
 			#echo $e->getMessage();
 			return "error";
 		}
 	}

 	public function save($info_save)
 	{
 		try {
 			$user_id = $this->getUserId();
 			$labels = [];
 			$values = [];
 			foreach ($info_save as $key => $value) {
 				$labels[] = "{$key}=?";
 				$values[] = $value;
 			}
 			$result = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE IGNORE `users` SET ".implode(',', $labels)." WHERE `id`={$user_id}", $values);
 			if($result==true) {
 				foreach ($info_save as $key => $value) {
 					$_SESSION['user'][$key] = $value;
 				}
 			};
 			return $result;
 		}
 		catch(Exception $e){
 			#echo $e->getMessage();
 			return false;
 		}
 	}

 	public function recover_pass($user_id)
 	{
 		try {
 			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `id`=? ";
 			$user = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$user_id]);
 			if($user == false) return $user;
 			$key = \randString(32);
 			$siteurl = \siteinfo('siteurl');
 			$urlrecover = "{$siteurl}/pacmec-recover-password?kr={$key}&ue=".urlencode($user->email);
 			$updated = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE IGNORE `users` SET `keyrecov`=? WHERE `id`={$user_id}", [$key]);
 			if($updated !== false){
 				if(!defined("PHP_EOL")) define("PHP_EOL", "\r\n");
 			  $email_contact_from = infosite('email_contact_from');
 			  $email_contact_received = $user->email;
 			  $e_subject = _autoT('recover_password_from');
 			  $template_org = file_get_contents(PACMEC_PATH.'templates-mails/recover-password.php', true);
 				$tags_in = [
 			    '%sitelogo%',
 			    '%sitename%',
 			    '%PreviewText%',
 			    '%recover_password_from%',
 			    '%recover_password_text%',
 			    '%display_name%',
 			    '%email%',
 			    '%urlrecover%',
 			    '%siteurl%',
 			    '%recover_password%',
 			  ];
 			  $tags_out = [
 			    infosite('sitelogo'),
 			    infosite('sitename'),
 			    infosite('sitedescr'),
 			    _autoT('recover_password_from'),
 			    _autoT('recover_password_text'),
 			    $user->display_name,
 			    $user->email,
 			    $urlrecover,
 			    infosite('siteurl').infosite('homeurl'),
 			    _autoT('recover_password'),
 			  ];
 			  $template = \str_replace($tags_in, $tags_out, $template_org);
 			  $mail = new PHPMailer(true);
 			  try {
 			      //Server settings
 			      //$mail->SMTPDebug = 2;                 // Enable verbose debug output
 			      $mail->isSMTP();                      // Set mailer to use SMTP
 			      $mail->Host       = SMTP_HOST;        // Specify main and backup SMTP servers
 			      $mail->SMTPAuth   = SMTP_AUTH;        // Enable SMTP authentication
 			      $mail->Username   = SMTP_USER;        // SMTP username
 			      $mail->Password   = SMTP_PASS;        // SMTP password
 			      $mail->SMTPSecure = SMTP_SECURE;      // Enable TLS encryption, `ssl` also accepted
 			      $mail->Port       = SMTP_PORT;        // TCP port to connect to
 			      $mail->CharSet    = infosite('charset');

 			      //Recipients
 			      $mail->setFrom($email_contact_from, infosite('sitename'));
 			      $mail->addAddress($email_contact_received);     // Add a recipient Name is optional (, 'name')
 			      // $mail->addReplyTo($email_contact_from, $e_subject);

 			      if(SMTP_CC!==false) $mail->addCC(SMTP_CC);
 			      if(SMTP_BCC!==false) $mail->addBCC(SMTP_BCC);

 						// Content
 			      $mail->isHTML(true);                                  // Set email format to HTML
 			      $mail->Subject = $e_subject;
 			      $mail->Body    = ($template);
 			      $mail->AltBody = \strip_tags($template);
 			      return ($mail->send());
 			  } catch (Exception $e) {
 			      return false;
 			  }
 			}
 		} catch (\Exception $e) {
 			return false;
 		}
 	}

 	public function change_pass($user_id, $password)
 	{
 		try {
 			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getTableName('users')}` WHERE `id`=? ";
 			$user = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$user_id]);
 			if($user == false) return $user;
 			$hash = password_hash($password, PASSWORD_DEFAULT);
 			$updated = $GLOBALS['PACMEC']['DB']->FetchObject("UPDATE IGNORE `users` SET `hash`=?,`keyrecov`=? WHERE `id`={$user_id}", [$hash,NULL]);
 			return $updated;
 		} catch (\Exception $e) {
 			return false;
 		}
	}
}
