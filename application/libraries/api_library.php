<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api_library
{
  /**
   * @var array $api_params. Allowed parameters - keys.
   * 
   */
  
  protected $api_params = ['token', 'method', 'db_group_name', 'id'];
  
  /**
   * @var array $allowed_http_methods. Allowed methods - url paths.
   * 
   */
  
  protected $allowed_http_methods = ['get', 'post', 'put', 'patch', 'sync'];
  
  /**
   * @var array $allowed_post_fields. Allowed post fields - the values to monitor.
   * 
   */
  
  protected $allowed_post_fields  = ['product_name','price']; 
  
  /**
   * @var stdClass $CI. Our to be CI instance.
   * 
   */
  
  private $CI;
  
  /**
   * @param stdClass $db With its help get db settings.
   * 
   */
  private $db;
  
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
    
    $this->db = get_db_parameter('db_master');
  }
  
  /**
   * @param function get_api_params() Provides API to export API params
   * 
   */
  
  public function get_api_params()
  {
    return $this->api_params;
  }
  
  /**
   * @param function get_allowed_post_fields() Provides available DB fields
   * 
   */
  
  public function get_allowed_post_fields()
  {
    return $this->allowed_post_fields;
  }
  
  // Main processing function
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
    
    // =======================
    // Just finish this part, think about how it is better to validate this whole shit
    // Then move on to batch operarions
    // =======================
    
    return $result;
  }
  
  protected function _process_request($params, $post_data)
  {
    $result = [];
    
    switch($params['method'])
    {
      case 'post':
        
        if (empty($post_data))
          $result = $this->CI->log_handler->log_error(109);
        
        else if (!empty($params['db_group_name']) && !empty($post_data[0]))
          $result = $this->CI->Api_model->post_collection($post_data, $params['db_group_name']);
        
        else
        {
          // Since product_name is unique, determine method to apply: post or update
          $result = $this->_record_exists($params, $post_data);
          
          if ($result === true)
          {
            $params['method'] = 'put';
            
            return $this->_process_request($params, $post_data);
          }
          
          $result = $this->CI->Api_model->post($post_data, $params['db_group_name']);
        }
        break;
        
      case 'get':
        
        if (!empty($params['db_group_name']))
          $result = $this->CI->Api_model->get($params);
        else
          $result = $this->CI->Api_model->get_collection();
        break;
      
      case 'put':
        
          $result = $this->CI->Api_model->update($post_data, $params['db_group_name']);
        break;
      
      case 'patch':
        
        if(!empty($params['db_group_name']) && !empty($params['db_group_name']))
          $result = $this->CI->Api_model->update_collection($post_data, $params['db_group_name']);
        break;
      
      case 'sync':
        $result = $this->CI->Api_model->sync();
        break;
    }
    
    return $result;
  }

  function _record_exists($params, $post_data)
  {
    $record = $this->CI->Api_model->get($params, $post_data);
    
    return (!empty($record)) ? true : false;
  }
  
  protected function _validate_db_group_name($params)
  {
    $db_array = get_db_parameter('db_master')->db_array;
    
    return (array_key_exists($params['db_group_name'], $db_array)) ?
      true : $result = $this->CI->log_handler->log_error(104);
  }
  
  protected function _validate_method($params)
  {
    return array_key_exists($params['method'], array_flip($this->allowed_http_methods)) ?
      true : $CI->log_handler->log_error(102);
  }
  
  protected function _validate_token($params)
  {
      return ($this->CI->Api_model->verify_token($params['token'])) ?
        true : $result = $this->CI->log_handler->log_error(101);
  }
  
  // Prevent injection in parameters
  protected function _validate_api_string($params)
  {
    $result = true;
    
    foreach ($params as $key => $param)
      (empty($param) || ctype_alnum($param)) ? true : $result = $this->CI->log_handler->log_error(103, $key);
    
    return $result;
  }
  
  public function _validate_post_data($post_data)
  {
    // Set vars
    // Call to database_helper
    $table_name = $this->db->tbl_name;
    
    // Error handling
    $this->CI->form_validation->set_error_delimiters('', '');
    
    $result = true;
    
    // Standard validation rules
    $standard_attributes = "required|xss_clean|max_length[64]";
    
    // Apply special validation rules
    $exceptions = [];
    $exceptions['product_name'] = "$standard_attributes|alpha_numeric";
    $exceptions['price']        = "$standard_attributes|numeric";
    
    $matched_keys_and_values = array_intersect_key($post_data, array_flip($this->allowed_post_fields));
    
    // Make sure all necessary keys are in the post
    if(count($this->allowed_post_fields) === count($matched_keys_and_values))
      $post_data = $matched_keys_and_values;
    
    // Validate input
    // Let's make it easier to add more fields for the future updates
    foreach($this->allowed_post_fields as $field_name)
    {
      // Apply general validation rules
      $this->CI->form_validation->set_rules($field_name, ucwords(str_replace('_',' ', $field_name)), $standard_attributes);
      
      // Apply exceptions
      foreach($exceptions as $field_name => $attribute)
        $this->CI->form_validation->set_rules($field_name, ucwords(str_replace('_',' ', $field_name)), $attribute);
    }
    
    // Setting error messages if there are errors
    if($this->CI->form_validation->run() === false)
    {
      $form_errors = [];
      
      foreach($this->allowed_post_fields as $field_name)
        if(form_error($field_name))
          $form_errors[$field_name] = form_error($field_name); //form_error(str_replace('_', ' ', $field_name));
      
      $result = $this->CI->log_handler->log_error(null, null, implode(' ', $form_errors));
    }
    else
    {
      $result = $post_data;
    }
    
    return $result;
  }
}

/* End of api_helper.php */