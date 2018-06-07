<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API library class to process get, post, update and insert requests and validate them.
 *
 * LICENSE:     This package is distributed under MIT License.
 * @package     Synca
 * @author      Arkai Pasternak <ap@nookeen.com> @ Nookeen Media
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     1.0
 * @link        https://github.com/nookeen/synca
 */

 class Api_library
{
  /**
   * Allowed parameters - keys.
   * 
   * @var array 
   */
  protected $api_params = ['token', 'method', 'db_group_name', 'id'];
  
  /**
   * Allowed methods - url paths.
   * 
   * @var array 
   */
  protected $allowed_http_methods = ['get', 'post', 'put', 'patch', 'sync'];
  
  /**
   * Allowed post fields - the values to monitor.
   * 
   * @var array
   */
  protected $allowed_post_fields  = ['product_name','price']; 
  
  /**
   * Our to be CI instance.
   * 
   * @var stdClass
   */
  private $CI;
  
  /**
   * @param stdClass $db With its help get db settings.
   * 
   */
  private $db;
  
   /**
   * Class constructor.
   *
   */
  function __construct()
  {
    // Get CI instance
    $this->CI =& get_instance();
    
    // Load Helpers
    $this->CI->load->helper(['database_helper', 'form']);
    
    // Load Libraries
    $this->CI->load->library(['form_validation', 'log_handler']);
    
    // Load Models
    $this->CI->load->model(['Api_model']);
    
    // Call to database_helper function
    $this->db = get_db_parameter('db_master');
  }
  
  /**
   * Provides API to export API params
   * 
   */
  public function get_api_params()
  {
    return $this->api_params;
  }
  
  /**
   * Provides available DB fields
   * 
   */
  public function get_allowed_post_fields()
  {
    return $this->allowed_post_fields;
  }
  
  /**
   * Main processing function
   * 
   * @param array $params API paramerts passed from URL
   * @param array $post_data If there were any _POST
   * 
   * @return array Final processed response from an API call
   */
  public function call($params, $post_data)
  {
    $result = false;
    
    // Validate method
    $result = $this->_validate_method($params);
    
    // Prevent injection in parameters
    if ($result === true)
      $result = $this->_validate_api_string($params);
    
    // Validate method
    if ($result === true)
      $result = $this->_validate_token($params);
    
    // Check if db exists
    if ($result === true && !empty($params['db_group_name']))
      $result = $this->_validate_db_group_name($params);
    
    // Validate post
    if ($result === true && !empty($post_data))
      $post_data = $this->_validate_post_data($post_data);
    
    if (!empty($post_data['status']))
      $result = $post_data;
    
    // Proceed to API request handling
    if ($result === true)
      $result = $this->_process_request($params, $post_data);
    
    return $result;
  }
  
  /**
   * Routes/or determines the method to use. Do remaining method-related validation here.
   * 
   * @param array $params API paramerts passed from URL
   * @param array $post_data If there were any _POST
   * 
   * @return array Processed response from DB interactions
   */
  protected function _process_request($params, $post_data)
  {
    $result = [];
    
    switch($params['method'])
    {
      case 'post':
        
        // If no _POST, throw error 
        if (empty($post_data))
          $result = $this->CI->log_handler->log_error(109);
        
        // Detect multiple inserts
        else if (!empty($params['db_group_name']) && !empty($post_data[0]))
          $result = $this->CI->Api_model->post_collection($post_data, $params['db_group_name']);
        
        // Then it's a single insert or update
        else
        {
          // Since product_name is unique, determine method to apply: post or update
          $result = $this->_record_exists($params, $post_data);
          
          // If record exsists already it's an UPDATE
          if ($result === true)
          {
            $params['method'] = 'put';
            
            return $this->_process_request($params, $post_data);
          }
          
          // Otherwise it's a POST
          $result = $this->CI->Api_model->post($post_data, $params['db_group_name']);
        }
        break;
        
      case 'get':
        
        // Determine whether to get a single record or the whole collection
        if (!empty($params['db_group_name']))
          $result = $this->CI->Api_model->get($params);
        else
          $result = $this->CI->Api_model->get_collection();
        
        break;
      
      case 'put':
        
        // Update a single record
        if (!empty($post_data) && !empty($params['db_group_name']))
          $result = $this->CI->Api_model->update($post_data, $params['db_group_name']);
        else
          $result = $this->CI->log_handler->log_error(110);
        
        break;
      
      case 'patch':
        
        // Update a single record
        if(!empty($post_data) && !empty($params['db_group_name']))
          $result = $this->CI->Api_model->update_collection($post_data, $params['db_group_name']);
        else
          $result = $this->CI->log_handler->log_error(110);
        
        break;
      
      case 'sync':
        
        // This is the SYNC functionality to sync all tables
        $result = $this->CI->Api_model->sync();
        break;
    }
    
    return $result;
  }

  /**
   * Checks if record already exist
   * 
   * @param array $params required API paramerts passed from URL
   * @param array $post_data required If there were any _POST
   *
   * @return boolean
   */
  function _record_exists($params, $post_data)
  {
    $record = $this->CI->Api_model->get($params, $post_data);
    
    return (!empty($record)) ? true : false;
  }
  
  /**
   * Checks if provided db_group_name exists
   * 
   * @param array $params required API paramerts passed from URL
   * 
   * @return mixed true or array with error
   */
  protected function _validate_db_group_name($params)
  {
    $db_array = get_db_parameter('db_master')->db_array;
    
    return (array_key_exists($params['db_group_name'], $db_array)) ?
      true : $result = $this->CI->log_handler->log_error(104);
  }
  
  /**
   * Checks if provided method exists
   * 
   * @param array $params required API paramerts passed from URL
   * 
   * @return mixed true or array with error
   */
  protected function _validate_method($params)
  {
    return array_key_exists($params['method'], array_flip($this->allowed_http_methods)) ?
      true : $CI->log_handler->log_error(102);
  }
  
  /**
   * Token validation. Reference DB to check if token matches.
   * 
   * @param array $params required API paramerts passed from URL
   * 
   * @return mixed true or array with error
   */
  protected function _validate_token($params)
  {
      return ($this->CI->Api_model->verify_token($params['token'])) ?
        true : $result = $this->CI->log_handler->log_error(101);
  }
  
  /**
   * Prevent injection in parameters.
   * 
   * @param array $params required API paramerts passed from URL
   * 
   * @return mixed true or array with error
   */
  protected function _validate_api_string($params)
  {
    $result = true;
    
    // All our parameters are alpha-numric, if it has anything else throw error
    foreach ($params as $key => $param)
      (empty($param) || ctype_alnum($param)) ? true : $result = $this->CI->log_handler->log_error(103, $key);
    
    return $result;
  }
  
  /**
   * _POST validation.
   * 
   * @param array $params required API paramerts passed from URL
   * 
   * @return mixed true or array with error
   */
  public function _validate_post_data($post_data)
  {
    // Get object's property from an object defined in constructor 
    $table_name = $this->db->tbl_name;
    
    // Error view handling
    $this->CI->form_validation->set_error_delimiters('', '');
    
    $result = [];
    
    // Standard validation rules
    $standard_attributes = "required|xss_clean|max_length[64]";
    
    // Apply special validation rules
    $exceptions = [];
    $exceptions['product_name'] = "$standard_attributes|alpha_numeric";
    $exceptions['price']        = "$standard_attributes|numeric";
    
    // Extract only allowed fields from the post
    $matched_keys_and_values = array_intersect_key($post_data, array_flip($this->allowed_post_fields));
    
    // Make sure all necessary keys are in the post
    if(count($this->allowed_post_fields) === count($matched_keys_and_values))
    {
      // Update $post_data to eliminate all extra fubmitted fields
      // like input submit buttons, extra validation fields.
      // Leave only fields that go into DB
      $post_data = $matched_keys_and_values;
    }
    // No need for else, since CI validation process will detect it

    // Let's make it easier to add more fields by automatically iterating and setting them
    foreach($this->allowed_post_fields as $field_name)
    {
      // Apply general validation rules
      $this->CI->form_validation->set_rules($field_name, ucwords(str_replace('_',' ', $field_name)), $standard_attributes);
      
      // Apply exceptions
      foreach($exceptions as $field_name => $attribute)
        $this->CI->form_validation->set_rules($field_name, ucwords(str_replace('_',' ', $field_name)), $attribute);
    }
    
    // Set error messages if there are errors
    if($this->CI->form_validation->run() === false)
    {
      $form_errors = [];
      
      // Again, let's make it easier to add more fields by automatically iterating and setting them
      foreach($this->allowed_post_fields as $field_name)
        if(form_error($field_name))
          $form_errors[$field_name] = form_error($field_name);
      
      // The view expects a single string for all errors, so we need to implode an array before returning
      $result = $this->CI->log_handler->log_error(null, null, implode(' ', $form_errors));
    }
    else
    {
      // If post validates, return updated results
      $result = $post_data;
    }
    
    return $result;
  }
}

/* End of api_helper.php */