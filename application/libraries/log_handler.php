<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Log_handler
{
  private function _get_content($status, $message_id, $param, $message)
  {
    $result = [];
    $messages = [];
    $messages['error'] = [
      
      // Note: 101-199 resevrved for API
      101 => 'Bad token.',
      102 => 'Bad method.',
      103 => "Error in the API string, non-alpha-numeric character present in $param.",
      104 => 'Bad db group name.',
      108 => 'Bad post data submitted.',
      109 => 'No post data submitted.',
      
      // DB operations 201-399
      201 => 'Could not post to DB.',
      202 => "Could not complete DB insert_batch post in DB: $param.",
      205 => "Could not complete DB update_batch post in DB: $param.",
      207 => 'DB operation failed: Required value is missing.',
      208 => 'Could not update record.',
      209 => 'Sync transaction process failed.',

      // Controller
      401 => 'Token or method are empty.',
      402 => '',
      403 => '',
      404 => '',
      405 => '',
      406 => '',
    ];
    
    $messages['success'] = [
      
      // Note: 101 - 299 resevrved for API
      201 => "Success adding a record to DB, id: $param.",
      202 => "Success syncing insert_batch in DB: $param.",
      205 => "Success syncing update_batch in DB: $param.",
      206 => "All DBs are in sync.",
      208 => 'Record updated.',
      
      // Controller
      401 => '',
      402 => '',
      403 => '',
      404 => '',
      405 => '',
      406 => '',
    ];
    
    $result['status'] = $status;
    
    $result['message'] = ($message_id === null) ? $message : $messages[$status][$message_id];
    
    return $result;
  }
  
  
  public function log_error($message_id, $param=null, $message=null)
  {
    return $this->_get_content('error', $message_id, $param, $message);
  }
  
  public function log_success($message_id, $param=null, $message=null)
  {
    return $this->_get_content('success', $message_id, $param, $message);
  }
}

/* End of log_hanlder.php */