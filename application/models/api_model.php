<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends CI_Model
{
  function __construct()
  {
    // Call the Model constructor
    parent::__construct();
    
    // Load Helpers
    $this->load->helper('database_helper');
    
    // Call to database_helper
    $this->db_master = get_db_parameter('db_master');
  }
  
  /**
   * Main sync() process function.
   *
   * @return array multidimentional. Response with all operations performed.
   * 
   */
  function sync()
  {
    /**
     * Main sync() process function.
     *
     * @param string $db_master
     * @param array $db_slaves_array
     *
     * Called from database_helper 
     */
    
    $db_master          = get_db_parameter('db_master')->dbmaster;
    $db_slaves_array    = get_db_parameter('db_slaves');
    
    $result = [];
    $data = [];
    $query = null;
    $timestamp_last_db_entry = '';

    /**
     * Begin transaction.
     *
     * Since it is a complex processing, we need to make sure
     * it would not cause problems and would cancel itself out
     * if it fails to complete
     * 
     */
    
    $this->db->trans_start();
    
    foreach ($db_slaves_array as $db_group_name)
    {
      
      $slave_records = [];
      $update_entries = [];
      $insert_entries = [];
      
      /**
       * @param array $timestamp_last_db_entry
       * 
       */
      
       $timestamp_last_db_entry = $this->get_timestamp_last_db_entry($db_group_name);
      
      /**
       * Get the records that have been updated after the last update to slave db
       * 
       * @param array $missing_records
       * 
       */
      
      $missing_records = $this->get_missing_records_from_master($db_master, $timestamp_last_db_entry['timestamp']);
      $list_of_missing_ids = [];
      
      /**
       * Compile a list of IDs to look into
       * 
       */
      
      foreach ($missing_records as $record)
        $list_of_missing_ids[] = $record['id'];
      
      /**
       * Check if some of those new entries are old one that have been updated
       * 
       * @param array $matched_records_from_slave
       * 
       */
      
      $matched_records_from_slave = $this->matching_missing_ids_with_slave($db_group_name, $list_of_missing_ids);
      
      /**
       * Make it easy to target needed IDs by plugging those in as keys
       * 
       */
      
      foreach ($matched_records_from_slave as $record)
        $slave_records[$record['id']] = $record;
      
      /**
       * This operation might seem confusing at first but since it's not looping through
       * the whole tables, it's actually ok
       * 
       * First step is to check if it's an insert or update
       * that needs to be performed on slave db
       * 
       */
      
      foreach ($missing_records as $record)
      {
        if ( !empty($slave_records[$record['id']]) )
        {
          /**
           * This compare each field from slave to each field from master
           * if there is a difference mark it for update
           * 
           * If they are the same the array is left alone and forgotten
           * 
           */
          
          $update = array_diff($record, $slave_records[$record['id']]);

          if ( !empty($update) )
            $update_entries[] = $record;
        }
        else
          $insert_entries[] = $record;
      }
      
      /**
       * Now just simply update and insert missing/updated entries into slave db
       * 
       */
      if (!empty($update_entries))
      {
        $result[] = $this->update_collection($update_entries, $db_group_name);
      }
      
      if (!empty($insert_entries))
      {
        $result[] = $this->post_collection($insert_entries, $db_group_name);
      }
      
      /**
       * However, if there was nothing done, it means we're good,
       * give a success message that everything is up-to-date
       * 
       */
      if (empty($result))
        $result[] = $this->log_handler->log_success(206);
    }
    
    $this->db->trans_complete();
    
    return ($this->db->trans_status() === false) ? $this->log_handler->log_error(209) : $result;
  }
  
  /**
   * @param function get_timestamp_last_db_entry
   * 
   * @return array
   */
  
  function get_timestamp_last_db_entry($db_group_name)
  {
    if(!$db_group_name)
      return $this->log_handler->log_success(207);;

    $data = [];
    $query = null;

    $db = $this->load->database($db_group_name, true);
    
    $db->select('timestamp');
    
    $db->order_by("timestamp", "desc");
    
    $db->limit(1);
    
    $query = $db->get($db->tbl_name);
    
    $data = $query->result_array()[0];
    
    $query->free_result();

    return $data;
  }

  /**
   * @param function get_missing_records_from_master
   * 
   * @return array
   */
  
  function get_missing_records_from_master($db_master, $timestamp_last_db_entry)
  {
    if(!$db_master || !$timestamp_last_db_entry)
      return $this->log_handler->log_success(207);;
    
    $data = [];
    $query = null;
    
    $db = $this->load->database($db_master, true);
    
    // Call to database_helper function
    $db->select(get_db_parameter('db_select'));
    
    $db->where('timestamp >=', $timestamp_last_db_entry);
    
    $db->order_by('timestamp', 'desc');
    
    $query = $db->get($db->tbl_name);
    
    $data = $query->result_array();
    
    $query->free_result();

    return $data;
  }

  /**
   * Try getting new/updated IDs from master from slave db, maybe they are just updates 
   * 
   * @param function matching_missing_ids_with_slave
   * 
   * @return array
   * 
   */
  
  function matching_missing_ids_with_slave($db_group_name, $list_of_missing_ids)
  {
    if(!$db_group_name || !$list_of_missing_ids)
      return $this->log_handler->log_success(207);;
    
    $data = [];
    $query = null;
    
    $db = $this->load->database($db_group_name, true);
    
    // Call to database_helper function
    $db->select(get_db_parameter('db_select'));
    
    $db->where_in('id', $list_of_missing_ids);
    
    $db->order_by("timestamp", "desc");
    
    $query = $db->get($db->tbl_name);
    
    $data = $query->result_array();
    
    $query->free_result();

    return $data;
  }

  /**
   * Token verification, let's improvise,
   * 
   * In production env it could be dene with real auth function
   * 
   */

  public function verify_token($token)
  {
    return ($token === APIKEY) ? true : false;
  }
  
  /**
   * Get data from any db
   * 
   * Requires db_group_name
   * 
   */

  function get($data, $where=null)
  {
    if(empty($data['db_group_name']))
    $data['db_group_name'] = $this->db_master->dbmaster;
    
    $result = [];
    $query = null;
    
    $db = $this->load->database($data['db_group_name'], true);
    
    // Call to database_helper function
    $db->select(get_db_parameter('db_select'));
    
    if ($where !== null)
      $db->where('product_name', $where['product_name']);
    
    $db->order_by("timestamp", "asc");
    
    $query = $db->get($db->tbl_name);
    
    $result = $query->result_array();
    
    $query->free_result();
    
    return $result;
  }
  
  /**
   * Get data from all DBs using the defined api get() function
   * 
   * @param function get_collection()
   */

  function get_collection()
  {
    $temp_data = [];
    $result = [];
    
    /**
     * @param array $db_array List of all db name groups
     * 
     */
    $db_array = $this->db_master->db_array;
    
    foreach ($db_array as $db_name)
    {
      /**
       * get() expects an array, so we create one with $temp_data
       * 
       */
      
      $temp_data['db_group_name'] = $db_name;
      
      $result[$db_name] = $this->get($temp_data);
    }
    
    return $result;
  }
  
  /**
   * Post data into db_group_name or by default into a MASTER table
   * 
   * @param array $post_data required
   * @param array $db_group_name optional
   * 
   * @param function post()
   */
  
   function post($post_data, $db_group_name=null)
  {
    /**
     * @param number $id Returned after successful insert
     * 
     */
    
    $id = null;
    
    if(empty($db_group_name))
      $db_group_name = $this->db_master->dbmaster;
    
    $db = $this->load->database($db_group_name, true);
    
    $db->insert($this->db_master->tbl_name, $post_data);
    $db->limit(1);
    $id = $db->insert_id();
    
    return (!empty($id)) ? $this->log_handler->log_success(201, $id) : $this->log_handler->log_error(201);
  }
  
  /**
   * Update data into db_group_name or by default into a MASTER table
   * 
   * @param array $post_data required
   * @param array $db_group_name optional
   * 
   * @param function update()
   */
  
  function update($post_data, $db_group_name=null)
  {
    if(empty($db_group_name))
      $db_group_name = $this->db_master->dbmaster;
    
    $db = $this->load->database($db_group_name, true);
    
    /**
     * Starting transaction to check if update was successful
     * 
     */
    
    $this->db->trans_start();
    
    /**
     * 'where' since product_name is unique, we can update based on it
     * 
     */
    
    $db->where('product_name', $post_data['product_name']);
    $db->limit(1);
    $db->update($this->db_master->tbl_name, $post_data);
    
    $this->db->trans_complete();

    return ($this->db->trans_status() === false) ? $this->log_handler->log_error(208) : $this->log_handler->log_success(208);
  }
  
  /**
   * Insert multiple rows
   * 
   * @param array $post_data required
   * @param array $db_group_name required
   * 
   * @param function post_collection()
   */
  
  function post_collection($post_data, $db_group_name)
  {
    if(empty($db_group_name))
      return $this->log_handler->log_success(207);

    $db = $this->load->database($db_group_name, true);
    
    /**
     * Starting transaction to check if update was successful
     * 
     */
    
    $db->trans_start();
    
    $db->insert_batch($db->tbl_name, $post_data);
    $db->trans_complete();
    $db->limit(1);
    
    /**
     * Show corresponding message in the end
     * 
     */
    
    return  ($db->trans_status() === false) ? $this->log_handler->log_error(202, $db_group_name) : $this->log_handler->log_success(202, $db_group_name);
  }
  
  /**
   * Update multiple rows
   * 
   * @param array $post_data required
   * @param array $db_group_name required
   * 
   * @param function update_collection()
   */
  
  function update_collection($post_data, $db_group_name)
  {
    $db = $this->load->database($db_group_name, true);
    
    /**
     * Starting transaction to check if update was successful
     * 
     */
    
    $db->trans_start();
    
    $db->update_batch($db->tbl_name, $post_data, 'id');
    $db->trans_complete();
    $db->limit(1);
    
    /**
     * Show corresponding message in the end
     * 
     */
    
    return ($db->trans_status() === false) ? $this->log_handler->log_error(205, $db_group_name) : $this->log_handler->log_success(205, $db_group_name);
  }
}
/* End of file api_model.php */
/* Location: ./application/models/api_model.php */