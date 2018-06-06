<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_db_parameter($param)
{
  // Get CI instance
  $CI =& get_instance();
  
  switch($param)
  {
    case 'db_select':
      return 'id, product_name, price, timestamp';
    case 'db_master':
      return $CI->load->database($CI->db->dbmaster, true);
    case 'db_slaves' :
      
      $db_array = $CI->db_master->db_array;
      
      unset($db_array[$CI->db_master->dbmaster]);
      
      return $db_array;
      
    case 'default':
      break;
  }
}

/* End of database_helper.php */