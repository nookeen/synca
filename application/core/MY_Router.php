<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class MY_Router extends CI_Router {
    
    function __construct()
    {
        parent::__construct();
    }
    	
	function _set_request ($seg = array())
    {
        // The str_replace() below goes through all our segments
        // and replaces the hyphens with underscores making it
        // possible to use hyphens in controllers, folder names and
        // function names
        parent::_set_request(str_replace('-', '_', $seg));
    }
}
 
/* End of file MY_Router.php */
/* Location: ./system/application/core/MY_Router.php */