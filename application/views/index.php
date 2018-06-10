<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Homepage view.
 *
 * LICENSE:     This package is distributed under MIT License.
 * @package     Synca
 * @author      Arkai Pasternak <ap@nookeen.com> @ Nookeen Media
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     1.0
 * @link        https://github.com/nookeen/synca
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Synca</title>
  <link rel='stylesheet' type='text/css' media='all' href="<?php print(site_url(ASSETS . CSS . 'style.css')); ?>" />
  <link rel='stylesheet' type='text/css' media='all' href="<?php print(site_url(ASSETS . CSS . VENDOR . 'bootstrap/bootstrap.min.css')); ?>" />
</head>
<body>
  
  <div style="display: block; width: 100%; padding: 1% 1% 0 1%;">
    <div class="alert alert-success alert-dismissible" role="alert" style="display: none;">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <div class="msg">cc</div>
    </div>
  </div>
  
<div class="container-fluid">
  <div class="container">
    <div class="col-12">
      
      <h1>Synca</h1>
      
      <h3>Easily manage sync for MySQL table</h3>
      
      <p><strong>Description:</strong> Data is added/updated on <strong>MASTER DB table</strong> constantly. The data from <strong>MASTER DB table</strong> needs to be synced with <strong>SLAVE DB(s) tables</strong>. Rather than deleting all entries from <strong>SLAVE DB(s) tables</strong>, Synca only inserts and updates entries which were added or modified.</p>
      
      <div class="row">
        <div class="col-lg-6 bg-highlight-lightgray padded-sm">
        
          <h4>Add/Update entries on MASTER DB</h4>
          <p>Where product name is unique</p>
          
          <?php print($form_open) ?>
          
          <div class="col-auto">
            <?php print($form_fields) ?>
          </div>
          <div class="col-auto">
            <?php print($form_button) ?>
          </div>
          
          <?php print($form_close) ?>
        
        </div>
        <div class="col-lg-6 bg-highlight-lightblue padded-sm">
        
          <h4>Sync and view changes</h4>
          <p>Check your changes</p>
          
          <div class="col-auto">
          <p>
            <a class="btn btn-info" id="runSync" href="<?php echo site_url('api/' . APIKEY . '/sync'); ?>">Run Sync Manually</a>
            <a class="btn btn-success" id="getCollection" href="<?php echo site_url('synca/view_all_data'); ?>" target="_blank">View all data</a>
          </p>
          </div>
        
        </div>
      </div>
      
      <p class="center"><strong>CRON</strong> script API access point: <span style="word-break: break-word;;"><?php echo site_url('api/' . APIKEY . '/sync'); ?></span></p>
      
    </div>
    
  </div>
  
  <br><br>
  
  <p class="center"><small>Page rendered in <strong>{elapsed_time}</strong> seconds and used {memory_usage}</small></p>
  
</div>

<script>
  var csrf_token = '<?php echo $this->security->get_csrf_hash(); ?>';
</script>
<script src="<?php print(site_url(ASSETS . JS . VENDOR . 'jquery/jquery-1.11.0.min.js')); ?>"></script>
<script src="<?php print(site_url(ASSETS . JS . 'app.js')); ?>"></script>

</body>
</html>