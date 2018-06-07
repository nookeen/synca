<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This helper provides additional DB information
 * needed throughout the application.
 *
 * LICENSE:     This package is distributed under MIT License.
 * @package     Synca
 * @author      Arkai Pasternak <ap@nookeen.com> @ Nookeen Media
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     1.0
 * @link        https://github.com/nookeen/synca
 */


/**
 * Function to return needed parameters stored in the CI DB object
 *  
 * @param string $param needed 
 * 
 * @return mixed Returns configs necessary to procss DB requests.
 */
function get_db_parameter($param)
{
  // Get CI instance
  $CI =& get_instance();
  
  switch($param)
  {
    // Keep a single standard select in one place
    case 'db_select':
      return 'id, product_name, price, timestamp';
    
    // Load master DB
    case 'db_master':
      return $CI->load->database($CI->db->dbmaster, true);
    
    // Return an array of SLAVES' DB tables
    case 'db_slaves' :
      
      $db_array = $CI->db_master->db_array;
      
      unset($db_array[$CI->db_master->dbmaster]);
      
      return $db_array;
      
    case 'default':
      break;
  }
}

/* End of database_helper.php */