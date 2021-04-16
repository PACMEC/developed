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
    /*
		$args = (array) $args;
		parent::__construct("routes", false);
		if(isset($args['id'])){ $this->getBy('id', $args['id']); }
		else if(isset($args['page_name'])){ $this->getBy('page_name', $args['page_name']); }
		else if(isset($args['page_slug'])){
			$args['page_slug'] = SELF::decodeURIautoT($args['page_slug']);
			$this->getBy('request_uri', $args['page_slug']);
		}
		else if(isset($args['request_uri'])){ $this->getBy('request_uri', $args['request_uri']); }
    */
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

	public static function allLoad() : array
  {
		$r = [];
		if(!isset($GLOBALS['PACMEC']['DB'])){ return $r; }
		foreach($GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM {$GLOBALS['PACMEC']['DB']->getPrefix()}routes ", []) as $menu){
			$r[] = new Self($menu);
		}
		return $r;
	}

	public function getBy($column='id', $val="")
  {
		try {
			$this->setAll($GLOBALS['PACMEC']['DB']->FetchObject("SELECT * FROM {$this->getTable()} WHERE `{$column}`=?", [$val]));
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
						// $permissions = \userinfo('permissions_items');
						// echo json_encode($permissions);

						$check = \validate_permission($arg['permission_access']);
						if($check == false){
							if(\isGuest()){
								$arg['component'] = 'pages-signin';
							} else {
								$arg['component'] = 'pages-error';
							}

							$arg['theme'] = 'system';
							$arg['content'] = 'no_access';
						}
						break;
				}
				foreach($arg as $k=>$v){
					# if($k=="content") $v = do_shortcode($v);
					switch ($k) {
						case 'page_slug':
							$this->{$k} = SELF::encodeURIautoT($v);
							break;
						default:
							$this->{$k} = ($v);
							break;
					}
				}
				$this->getMeta();
				$this->getComponents();
			}
		}
	}

	public function isValid()
	{
		return $this->id > 0 ? true : false;
	}

  public function getComponents()
  {
    try {
      if($this->id>0){
        $result = $GLOBALS['PACMEC']['DB']->FetchAllObject("SELECT * FROM `{$this->getTable()}_components` WHERE `route_id`=? ORDER BY `ordering` ASC", [$this->id]);
        if(is_array($result)) {
          $this->components = [];
          foreach ($result as $component) {
            $component->data = json_decode($component->data);
            $this->components[] = $component;
          }
        }
        return [];
      }
    }
    catch(\Exception $e){
      return [];
    }
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
