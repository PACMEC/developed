<?php
/**
 *
 * @author     FelipheGomez <feliphegomez@gmail.com>
 * @package    PACMEC
 * @category   Helper
 * @copyright  2020-2021 Manager Technology CO
 * @license    license.txt
 * @version    Release: @package_version@
 * @link       http://github.com/ManagerTechnologyCO/PACMEC
 * @version    1.0.1
 */

namespace PACMEC;

interface EntidadBase {
  public function __construct();
  public function __sleep();
  public function __toString();
  public function setTable($t);
  public function modelInitial($s);
  public function loadColumns();
  public function get_value_default_sql($t, $d);
  public function getColumns($i);
  public function filterEq($i);
  public function isValid();
  public function setAll($a);
  public function getRand($l);
  public function getBy($c, $v);
  public function getAdapter();
}
