<?php
/**
 *
 * @package    PACMEC
 * @category   Functions
 * @copyright  2020-2021 Manager Technology CO & FelipheGomez CO
 * @author     FelipheGomez <feliphegomez@gmail.com>
 * @license    license.txt
 * @version    0.0.1
 */

function siteinfo($option_name)
{
  global $PACMEC;
	if(!isset($PACMEC['options'][$option_name])){
		return 'NaN';
		// return "{$option_name}";
	}
	return html_entity_decode($PACMEC['options'][$option_name]);
}
function infosite($option_name){
  return siteinfo($option_name);
}

function checkFolder($path)
{
  if(!is_dir($path)) mkdir($path, 0755);
  if(!is_dir($path)) { echo "No se puede acceder o crear -> $path"; exit; }
  return true;
}

function activation_plugin($plugin){
  return \do_action('activate_' . $plugin);
}

function register_activation_plugin($plugin, $function){
  return \add_action( 'activate_' . $plugin, $function );
}

function validate_theme($theme):bool
{
  global $PACMEC;
  return in_array($theme, array_keys($PACMEC['themes']));
}

function activation_theme($theme)
{
  global $PACMEC;
  if(validate_theme($theme)==true && $PACMEC['themes'][$theme]['active'] == false){
    require_once $PACMEC['themes'][$theme]['file'];
    //$PACMEC['themes'][$theme]['active'] = true;
    $PACMEC['themes'][$theme]['active'] = \do_action('activate_' . $theme);
    if($PACMEC['themes'][$theme]['active'] == true){
      $PACMEC['theme'] = $PACMEC['themes'][$theme];
    }
    return $PACMEC['themes'][$theme]['active'];
  }
  return false;
}

function register_activation_theme($theme, $function){
  return \add_action( 'activate_' . $theme, $function );
}

/**
*
* Traduccion automatica
*
* @param string   $label       *
* @param string   $lang        (Optional)
*
* @return string
*/
function pacmec_translate_label($label, $lang=null) : string
{
  try {
    /*
      if(isset($GLOBALS['PACMEC']['glossary_txt'][$label])) return $GLOBALS['PACMEC']['glossary_txt'][$label];
      $glossary_id = $GLOBALS['PACMEC']['glossary'][$GLOBALS['PACMEC']["lang"]]['id'];
      $slug = $label;
      $text = "þ{{$label}}";
    $sql_ins = "INSERT INTO `{$GLOBALS['PACMEC']['DB']->getPrefix()}glossary_txt` (`glossary_id`, `slug`, `text`) SELECT * FROM (SELECT {$glossary_id},'{$slug}','{$text}') AS tmp WHERE NOT EXISTS (SELECT `id` FROM `{$GLOBALS['PACMEC']['DB']->getPrefix()}glossary_txt` WHERE `glossary_id` = '{$glossary_id}' AND `slug` = '{$slug}') LIMIT 1";
    $insert = $GLOBALS['PACMEC']['DB']->FetchObject($sql_ins, []);
    if($insert>0){
      return "þ{ $label }";
    }*/
    return "þE{ $label }";
  } catch (\Exception $e) {
    return "Þ{ $label }";
  }
}

/**
*
* Alias rápido para Traduccion
*
* @param string $label Label a traduccir
*
* @return string
*/
function _autoT($label) : string
{
  return pacmec_translate_label($label);
}

function pacmec_exist_meta($meta)
{
  return false;
}

function pacmec_add_meta_tag($name_or_property_or_http_equiv_or_rel, $content, $ordering=0.35, $atts=[])
{
  switch ($name_or_property_or_http_equiv_or_rel) {
    case 'title':
    case 'description':
    case 'url':
    if($name_or_property_or_http_equiv_or_rel == 'title') $GLOBALS['PACMEC']['website']['meta'][] = [ "tag" => "title", "content" => $content, "attrs" => [], "ordering" => $ordering ];
      //
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "name" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      pacmec_add_meta_tag('og:'.$name_or_property_or_http_equiv_or_rel, $content);
      break;
    case 'keywords':
    case 'language':
    case 'robots':
    case 'Classification':
    case 'author':
    case 'designer':
    case 'copyright':
    case 'reply-to':
    case 'owner':
    case 'Expires':
    case 'Pragma':
    case 'Cache-Control':
    case 'generator':
    case 'fb:page_id':
    case 'og:title':
    case 'og:url':
    case 'og:site_name':
    case 'og:description':
    case 'og:email':
    case 'og:phone_number':
    case 'og:fax_number':
    case 'og:latitude':
    case 'og:longitude':
    case 'og:street-address':
    case 'og:locality':
    case 'og:region':
    case 'og:postal-code':
    case 'og:country-name':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "name" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    case 'image':
      pacmec_add_meta_tag('og:image', $content);
      break;
    case 'og:type':
    case 'og:image':
    case 'og:points':
    case 'og:video':
    case 'og:video:height':
    case 'og:video:width':
    case 'og:video:type':
    case 'og:audio':
    case 'og:audio:title':
    case 'og:audio:artist':
    case 'og:audio:album':
    case 'og:audio:type':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "meta", "attrs" => array_merge($atts, [ "property" => $name_or_property_or_http_equiv_or_rel, "content" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    case 'favicon':
      $GLOBALS['PACMEC']['website']['meta'][] = [
        "tag" => "link", "attrs" => array_merge($atts, [ "rel" => "shortcut icon", "href" => $content ]),
        "ordering" => $ordering, "content" => "",
      ];
      break;
    default:
      break;
  }
}

function add_style_head($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['styles']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['styles']['head'][] = [
      "tag" => "link",
      "attrs" => array_merge($attrs, [
        "href" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function add_style_foot($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['styles']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['styles']['foot'][] = [
      "tag" => "link",
      "attrs" => array_merge($attrs, [
        "href" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function add_scripts_head($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['scripts']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['scripts']['head'][] = [
      "tag" => "script",
      "attrs" => array_merge($attrs, [
        "src" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function add_scripts_foot($src, $attrs = [], $ordering = 0.35, $add_in_list = false)
{
  if(!isset($attrs) || $attrs==null || !is_array($attrs)) $attrs = [];
  if(!isset($ordering) || $ordering==null) $ordering = 0.35;
  if(!isset($add_in_list) || $add_in_list==null) $add_in_list = false;
  if ($src) {
    if($add_in_list == true) $GLOBALS['PACMEC']['website']['scripts']['list'][] = $src;
		$GLOBALS['PACMEC']['website']['scripts']['foot'][] = [
      "tag" => "script",
      "attrs" => array_merge($attrs, [
        "src" => $src,
        "ordering" => $ordering,
      ]),
      "ordering" => $ordering,
    ];
		return true;
	}
	return false;
}

function stable_usort(&$array, $cmp)
{
  $i = 0;
  $array = array_map(function($elt)use(&$i)
  {
      return [$i++, $elt];
  }, $array);
  usort($array, function($a, $b)use($cmp)
  {
      return $cmp($a[1], $b[1]) ?: ($a[0] - $b[0]);
  });
  $array = array_column($array, 1);
}


function pacmec_head()
{
  do_action('meta_head');
  stable_usort($GLOBALS['PACMEC']['website']['styles']['head'], 'pacmec_ordering_by_object_asc');
  stable_usort($GLOBALS['PACMEC']['website']['scripts']['head'], 'pacmec_ordering_by_object_asc');
  do_action( "head" );
  $a = "";
  foreach($GLOBALS['PACMEC']['website']['styles']['head'] as $file){ $a .= \PHPStrap\Util\Html::tag($file['tag'], "", [], $file['attrs'], true)."\t"; }
  $a .= \PHPStrap\Util\Html::tag('style', do_action( "head-styles" ), [], ["rel"=>"stylesheet", "type"=>"text/css", "charset"=>"UTF-8"], false) . "\t";
  foreach($GLOBALS['PACMEC']['website']['scripts']['head'] as $file){ $a .= \PHPStrap\Util\Html::tag($file['tag'], "", [], $file['attrs'], false)."\t"; }
  echo "<script type=\"text/javascript\">\n\t\t";
  echo '/* Scripts PACMEC */'."\n\t";
  echo "</script>\n\t";
  echo "{$a}";
  echo "<script type=\"text/javascript\">";
  do_action( "head-scripts" );
  echo "</script>";
  echo "\n";
	return true;
}


function language_attributes()
{
	return "class=\"".siteinfo('html_type')."\" lang=\"{$GLOBALS['PACMEC']['lang']}\"";
}

function pageinfo($key)
{
	return isset($GLOBALS['PACMEC']['route']->{$key}) ? "{$GLOBALS['PACMEC']['route']->{$key}}" : siteinfo($key);
}





/** Session and Me **/
function isAdmin() : bool
{
	return ((isUser() && validate_permission('super_user')) || (isset($_SESSION['user']['is_admin'])&&$_SESSION['user']['is_admin']===1)) ? true : false;
}

function isUser() : bool
{
	return !(isGuest());
}

function isGuest() : bool
{
	return !isset($_SESSION['user']) || !isset($_SESSION['user']['id']) || $_SESSION['user']['id']<=0 ? true : false;
}

function userID() : int
{
  return isUser() ? $_SESSION['user']['id'] : 0;
}

function userinfo($option_name){
  global $PACMEC;
	return userID()>0&&isset($PACMEC['session']->{$option_name}) ? $PACMEC['session']->{$option_name} : "unk";
}

function meinfo(){
  global $PACMEC;
	return userID()>0 ? $PACMEC['session'] : [];
}

function validate_permission($permission_label){
  global $PACMEC;
	if($permission_label == "guest"){ return true; }
  if(userID()<=0){ return false; }
  return in_array($permission_label, userinfo('permissions'));
}


/** Short access Hooks **/
/**
 * Execute functions hooked on a specific action hook.
 *
 * @param    string $tag     <p>The name of the action to be executed.</p>
 * @param    mixed  $arg     <p>
 *                           [optional] Additional arguments which are passed on
 *                           to the functions hooked to the action.
 *                           </p>
 *
 * @return   bool            <p>Will return false if $tag does not exist in $filter array.</p>
 */
function do_action(string $tag, $arg = ''): bool {
  global $PACMEC;
	return $PACMEC['hooks']->do_action($tag, $arg);
}

/**
 * Hooks a function on to a specific action.
 *
 * @param    string       $tag              <p>
 *                                          The name of the action to which the
 *                                          <tt>$function_to_add</tt> is hooked.
 *                                          </p>
 * @param    string|array $function_to_add  <p>The name of the function you wish to be called.</p>
 * @param    int          $priority         <p>
 *                                          [optional] Used to specify the order in which
 *                                          the functions associated with a particular
 *                                          action are executed (default: 50).
 *                                          Lower numbers correspond with earlier execution,
 *                                          and functions with the same priority are executed
 *                                          in the order in which they were added to the action.
 *                                          </p>
 * @param     string      $include_path     <p>[optional] File to include before executing the callback.</p>
 *
 * @return bool
 */
function add_action(string $tag, $function_to_add, int $priority = 50, string $include_path = null) : bool
{
  global $PACMEC;
	return $PACMEC['hooks']->add_action($tag, $function_to_add, $priority, $include_path);
}

/**
 * Add hook for shortcode tag.
 *
 * <p>
 * <br />
 * There can only be one hook for each shortcode. Which means that if another
 * plugin has a similar shortcode, it will override yours or yours will override
 * theirs depending on which order the plugins are included and/or ran.
 * <br />
 * <br />
 * </p>
 *
 * Simplest example of a shortcode tag using the API:
 *
 * <code>
 * // [footag foo="bar"]
 * function footag_func($atts) {
 *  return "foo = {$atts[foo]}";
 * }
 * add_shortcode('footag', 'footag_func');
 * </code>
 *
 * Example with nice attribute defaults:
 *
 * <code>
 * // [bartag foo="bar"]
 * function bartag_func($atts) {
 *  $args = shortcode_atts(array(
 *    'foo' => 'no foo',
 *    'baz' => 'default baz',
 *  ), $atts);
 *
 *  return "foo = {$args['foo']}";
 * }
 * add_shortcode('bartag', 'bartag_func');
 * </code>
 *
 * Example with enclosed content:
 *
 * <code>
 * // [baztag]content[/baztag]
 * function baztag_func($atts, $content='') {
 *  return "content = $content";
 * }
 * add_shortcode('baztag', 'baztag_func');
 * </code>
 *
 * @param string   $tag  <p>Shortcode tag to be searched in post content.</p>
 * @param callable $callback <p>Hook to run when shortcode is found.</p>
 *
 * @return bool
 */
function add_shortcode($tag, $callback) : bool
{
	if($GLOBALS['PACMEC']['hooks']->shortcode_exists($tag) == false){
		/*
		if(!isset($_GET['editor_front'])){
		} else {
			return $GLOBALS['PACMEC']['hooks']->add_shortcode( $tag, function() use ($tag) { echo "[{$tag}]"; } );
			return true;
		};*/
		return $GLOBALS['PACMEC']['hooks']->add_shortcode( $tag, $callback );
	} else {
		return false;
	}
}

/**
*
* Add
*
* @param array   $pairs       *
* @param array   $atts        *
* @param string  $shortcode   (Optional)
*
* @return array
*/
function shortcode_atts($pairs, $atts, $shortcode = ''): array
{
	return $GLOBALS['PACMEC']['hooks']->shortcode_atts($pairs, $atts, $shortcode);
}

/**
 * Adds Hooks to a function or method to a specific filter action.
 *
 * @param    string              $tag             <p>
 *                                                The name of the filter to hook the
 *                                                {@link $function_to_add} to.
 *                                                </p>
 * @param    string|array|object $function_to_add <p>
 *                                                The name of the function to be called
 *                                                when the filter is applied.
 *                                                </p>
 * @param    int                 $priority        <p>
 *                                                [optional] Used to specify the order in
 *                                                which the functions associated with a
 *                                                particular action are executed (default: 50).
 *                                                Lower numbers correspond with earlier execution,
 *                                                and functions with the same priority are executed
 *                                                in the order in which they were added to the action.
 *                                                </p>
 * @param string                 $include_path    <p>
 *                                                [optional] File to include before executing the callback.
 *                                                </p>
 *
 * @return bool
 */
function add_filter(string $tag, $function_to_add, int $priority = 50, string $include_path = null): bool
{
  return $GLOBALS['PACMEC']['hooks']->add_filter($tag, $function_to_add, $priority, $include_path);
}
