<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main Synca controller class that handles all routes and posts.
 *
 * LICENSE:     This package is distributed under MIT License.
 * @package     Synca
 * @author      Arkai Pasternak <ap@nookeen.com> @ Nookeen Media
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     1.0
 * @link        https://github.com/nookeen/synca
 */


class Synca extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    
    // Load Helpers
    $this->load->helper(['form', 'url', 'database_helper']);
    
    // Load Libraries
    $this->load->library(['form_validation', 'api_library']);
    
    // Load Models
    $this->load->model(['Api_model']);
    
    // Call to database_helper
    $this->db_master = get_db_parameter('db_master');
  }
  
  /**
   * Main home function.
   *
   */
  public function index()
  {
    /**
     * Dynamically build a form with Spark plugin
     *
     * Load Spark
     * 
     */
    
    $this->load->spark('autoform/3.8.2');
    
    $data = [];
    
    /**
     * @param array $allowed_post_fields Form fields to include on the form
     *
     */
    
    $allowed_post_fields = $this->api_library->get_allowed_post_fields();
    
    /**
     * The form loads fields from master db
     * 
     * @param string $table_name Get the name of the table in master db
     *
     * $this->db_master defined in constructor
     */
    
    $table_name = $this->db_master->tbl_name;
    
    /**
     * Build the form
     *
     */
    $this->autoform->table($table_name);
    
    $data['form_open'] = $this->autoform->open('/api/' . APIKEY . '/post/', ['id'=>'form', 'class'=>'form-inline'], false);
    
    /**
     * Set fields
     *
     */
    foreach($allowed_post_fields as $field_name)
      $this->autoform->set($field_name, [
        'class'       => 'form-control form-element', 
        'label'       => ['class'=>'sr-only'], 
        'placeholder' => ucwords(str_replace('_',' ', $field_name)),
      ]);
    
    $data['form_fields'] = $this->autoform->fields($allowed_post_fields);
    
    $data['form_button'] = $this->autoform->buttons(form_submit([
        'type'    => 'submit',
        'name'    => 'add',
        'id'      => 'add',
        'value'   => 'Add',
        'class'   => 'btn btn-primary',
      ])
    );
    
    $data['form_close'] = $this->autoform->close();
    
    /**
     * Pass it to view
     *
     */
    $this->load->view('index', $data);
  }
  
  /**
   * Main API function.
   * 
   * @param string $token
   * @param string $method
   * @param string $db_group_name Name of the database to query
   * @param string $id
   *
   */
  public function api($token=null, $method=null, $db_group_name=null, $id=null)
  {
    $data = [];
    
    if (empty($token) || empty($method))
    {
      $result = $this->log_handler->log_error(401);
    }
    else
    {
      $get_api_params = $this->api_library->get_api_params();
      
      /**
       * Dynamically create a $data with API params
       *
       */
      foreach ($get_api_params as $param)
        $data[$param] = ${$param};
      
      $post_data = $this->input->post(null, true);
        
      /**
       * Process API request
       *
       * @param function call() is the main function to process requests
       */
      $result = $this->api_library->call($data, $post_data);
    }
    
    /**
     * Every API response comes here,
     * 
     * it is JSON encoded only here and then printed
     *
     */
    
    print(json_encode($result));
    
    exit;
  }
  
  /**
   * This functions view_all_data() is used to view
   * 
   * all data from MySQL tables,
   *
   */
  public function view_all_data()
  {
    $data = [];
    $result = file_get_contents(site_url('api/' . APIKEY . '/get/'));
    $result = json_decode($result, true);
    
    $data['result'] = $result;
    
    // Pass it to view
    return $this->load->view('view_all_data', $data);
  }
}

/* End of file synca.php */
/* Location: ./application/controllers/synca.php */