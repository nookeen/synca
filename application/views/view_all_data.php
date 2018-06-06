<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Synca</title>
  <link rel='stylesheet' type='text/css' media='all' href="<?php print(site_url(ASSETS . CSS . 'style.css')); ?>" />
  <link rel='stylesheet' type='text/css' media='all' href="<?php print(site_url(ASSETS . CSS . VENDOR . 'bootstrap/bootstrap.min.css')); ?>" />
</head>
<body>

<div style="margin: 1%;">
  <a href="<?php print(site_url()); ?>"> << BACK </a>
</div>

<div style="clear: both;"></div>

<?php if ( isset($result) ) : ?>
  <?php foreach ($result as $db_group_name => $db_array_content) : ?>

<div style="width: 30%; min-width: 320px; margin: 1%; display: inline-block;">
  
  <h2><?php print($db_group_name); ?></h2>
  
  <table class="datatables">
    <thead>
      <tr>
        <th>Id</th>
        <th>Product name</th>
        <th>Price</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    
    <?php foreach ($db_array_content as $val) : ?>
    
    <tr class="datatablesRow">
      <td class="<?php print(''); ?>"><?php print($val['id']); ?></td>
      <td class="<?php print(''); ?>"><?php print($val['product_name']); ?></td>
      <td class="<?php print(''); ?>"><?php print($val['price']); ?></td>
      <td class="<?php print(''); ?>"><?php print($val['timestamp']); ?></td>
    </tr>
    
    <?php endforeach; ?>
  
  </table>
</div>

<?php
  endforeach;
endif;
?>

<div style="margin: 1% 1% 3% 1%;">
  <a href="<?php print(site_url()); ?>"> << BACK </a>
</div>

</body>
</html>