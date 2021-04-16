<?php
/**
 *
 * @package    PACMEC
 * @category   Session
 * @copyright  2020-2021 Manager Technology CO & FelipheGomez CO
 * @author     FelipheGomez <feliphegomez@gmail.com>
 * @license    license.txt
 * @version    0.0.1
 */
namespace PACMEC;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
Class Session
{
	#public  $isGuest          = true;
	public  $user               = null;
	public  $payment            = null;
	public  $payments           = [];
	public  $permission_group   = null;
	public  $permissions_items  = [];
	public  $permissions        = [];
	public  $notifications      = [];
	public  $purses_to_send     = [];
	public  $emails_boxes       = [];

	/**
	* Inicializa la sesión
	*/
	public function __construct()
	{
		//$this->pacmecDB = $GLOBALS['PACMEC']['DB'];
		if ( $this->is_session_started() === FALSE ) @\session_start();
		$this->user             = new \stdClass();
		$this->permission_group = new \stdClass();
		$this->refreshSession();
	}

	public function add_alert(string $message, string $title=null, string $url=null, int $time=null, string $uniqid=null, string $icon=null)
	{
		$time = $time==null ? time() : $time;
		$uniqid = $uniqid==null ? \uniqid() : $uniqid;
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

	public function is_session_started()
	{
		if ( \php_sapi_name() !== 'cli' ) {
			if ( \version_compare(\phpversion(), '5.4.0', '>=') ) { return \session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE; }
			else { return \session_id() === '' ? FALSE : TRUE; }
		}
		return FALSE;
	}

	public function refreshSession()
	{
		if(isUser()){
			try {
				if(isset($_SESSION['user'])){
					$this->setAll($_SESSION);
				}
			}
			catch(Exception $e){
				echo $e->getMessage();
				exit();
			}
		}
	}

	public function setAll($session = [])
	{
		$session = (array) $session;
		foreach($session as $item => $valor){
			switch($item){
				case "user":
				case "permission_group":
					$valor = (object) $valor;
					break;
				case "permissions_items" || "permissions" || "notifications":
					$valor = (array) $valor;
					break;
				default:
					$valor = (is_object($valor)) ? (object) $valor : $valor;
					break;
			}
			$this->{$item} = (($valor));
		}
		if(isset($this->user->permissions) && $this->user->permissions !== null && $this->user->permissions > 0 && count($this->permissions)==0){
			$sql = "SELECT E.*
				FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}permissions` D
				JOIN `{$GLOBALS['PACMEC']['DB']->getPrefix()}permissions_items` E
				ON E.`id` = D.`permission`
				WHERE D.`group` IN (?)";
			$result = $GLOBALS['PACMEC']['DB']->FetchAllObject($sql, [$this->user->permissions]);
			if($result !== false && count($result) > 0){
				foreach($result as $perm){
					$this->add_permission($perm->tag, $perm);
				}
			}
			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}permissions_group` WHERE `id` IN (?)";
			$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$this->user->permissions]);
			if($result !== false){
				$this->permission_group = $result;
			}
		}

		$sql = "SELECT E.*
			FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}permissions_users` D
			JOIN `{$GLOBALS['PACMEC']['DB']->getPrefix()}permissions_items` E
			ON E.`id` = D.`permission`
			WHERE D.`user_id` IN (?)";
		$result = $GLOBALS['PACMEC']['DB']->FetchAllObject($sql, [$this->user->id]);
		if($result !== false && count($result) > 0){
			foreach($result as $perm){
				$this->add_permission($perm->tag, $perm);
			}
		}

		foreach ($this as $k => $v) {
			$_SESSION[$k] = $v;
		}
	}

	public function isGuest()
	{
		return isset($_SESSION['user']) && is_array($_SESSION['user']) ? false : true;
	}

	public function userId()
	{
		return isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0;
	}

	public function getBy($key)
	{
		return isset($_SESSION['user']) ? $this->{$key} : null;
	}

	public function getUserBy($key)
	{
		#$this->refreshSession();
		return isset($_SESSION['user']) ? $this->user->{$key} : null;
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

	public function getUserId()
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
			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `username`=? AND `status` IN (1) ";
			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `username`=? ";
			$result = $GLOBALS['PACMEC']['DB']->FetchObject($sql, [$nick_or_email]);
			if($result == false){
				$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `email`=? AND `status` IN (1) ";
				$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `email`=? ";
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
			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `keyrecov` IN (?) AND `email` IN (?) ";
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
			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `id`=? ";
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
			$sql = "SELECT * FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}users` WHERE `id`=? ";
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
