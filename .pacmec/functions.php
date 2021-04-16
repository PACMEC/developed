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
		return "{$option_name}-undefined";
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
function add_action(string $tag, $function_to_add, int $priority = 50, string $include_path = null) : bool {
	return $GLOBALS['PACMEC']['hooks']->add_action($tag, $function_to_add, $priority, $include_path);
}
