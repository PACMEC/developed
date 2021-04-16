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

class Route extends \PACMEC\System\ModeloBase
{
	public $id = -1;
	public $is_actived = 1;
	public $parent = null;
	public $permission_access = null;
	public $title = 'no_found';
	public $theme = null;
	public $description = 'No Found';
	public $content = '';
	public $request_uri = '/404';
	public $request_host = '/404';
	public $layout = 'pages-error';
	public $meta = [];

	public function __construct($args=[])
  {
		$args = (array) $args;
		parent::__construct("routes", false);
		if(isset($args['id'])){ $this->getPublicBy('id', $args['id']); }
		else if(isset($args['request_uri'])){ $this->getPublicBy('request_uri', $args['request_uri']); }
	}

	public static function encodeURIautoT(string $page_slug) : string
	{
		$url_explode = explode('/', $page_slug);
		if(!isset($url_explode[1]) || empty($url_explode[1])) return $page_slug;
		switch ($url_explode[1]) {
			case ('%autot_services%'):
				$url_explode[1] = _autoT('%autot_services%');
				break;
			default:
				break;
		}
		return implode('/', $url_explode);
	}

	public static function decodeURIautoT(string $page_slug) : string
	{
		$url_explode = explode('/', $page_slug);
		switch ($url_explode[1]) {
			case _autoT('%autot_services%'):
				$url_explode[1] = '%autot_services%';
				break;
			default:
				break;
		}
		return implode('/', $url_explode);
	}

	public static function allLoad() : Array
  {
		$r = [];
		if(!isset($GLOBALS['PACMEC']['DB'])){ return $r; }
		foreach($GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM {$this->getTable()}", []) as $menu){
			$r[] = new Self($menu);
		}
		return $r;
	}

	public function getBy($a,$b)
	{
		return $this->getPublicBy($a,$b);
	}

	public function getById($a)
	{
		return $this->getPublicBy('id',$a);
	}

	public function getPublicBy($column='id', $val="")
  {
		try {
			global $PACMEC;
			$this->setAll(Self::FetchObject(
				"SELECT * FROM {$this->getTable()}
					WHERE `{$column}`=?
					AND `host` IN ('*', ?)
					"
				, [
					$val,
					$PACMEC['host']
				]
			));
			return $this;
		}
		catch(\Exception $e){
			return $this;
		}
	}

	function setAll($arg=null)
	{
		if($arg !== null){
			if(\is_object($arg) || \is_array($arg)){
				$arg = (array) $arg;
				switch ($arg['permission_access']) {
					case null:
						break;
					default:
						$check = \validate_permission($arg['permission_access']);
						if($check == false){
							//if(\isGuest()){ $arg['layout'] = 'pages-signin'; } else { $arg['layout'] = 'pages-error'; }
							$arg['layout'] = 'pages-error';
							$arg['content'] = "[pacmec-errors title=\"route_no_access_title\" content=\"route_no_access_content\"][/pacmec-errors]";
						}
						break;
				}
				foreach($arg as $k=>$v){
					switch ($k) {
						case 'page_slug':
							$this->{$k} = SELF::encodeURIautoT($v);
							break;
						default:
							$this->{$k} = ($v);
							break;
					}
				}
				if($this->is_actived == 0){
					$this->layout = 'pages-error';
					$this->content = "[pacmec-errors title=\"route_no_actived_title\" content=\"route_no_actived_content\"][/pacmec-errors]";
				}
				//$this->getMeta();
			}
		}
		if(is_null($this->theme)) $this->theme = \infosite('theme_default');
		if(\validate_theme($this->theme)==false) $this->theme = \infosite('theme_default');
		$acti = \activation_theme($this->theme);
		if($this->id <= 0){
			$this->layout = 'pages-error';
			$this->content = "[pacmec-errors title=\"error_404_title\" content=\"error_404_content\"][/pacmec-errors]";
		}
	}

	public function isValid()
	{
		return $this->id > 0 ? true : false;
	}

  public function getMeta()
  {
    try {
      if($this->id>0){
        $result = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$this->getTable()}_meta` WHERE `route_id`=? ORDER BY `ordering` DESC", [$this->id]);
        if(is_array($result)) {
          $this->meta = [];
          foreach ($result as $meta) {
            $meta->attrs = json_decode($meta->attrs);
            $this->meta[] = $meta;
          }
        }
        return [];
      }
    }
    catch(\Exception $e){
      return [];
    }
  }
}
