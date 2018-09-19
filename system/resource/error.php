<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title> <?php echo $error_type; ?> | Powered By Suda System </title>
  <style>
.error-wrapper{margin:0 auto;padding:0;border:0;text-align:left;color:#404040;font-family:"Microsoft YaHei",Helvetica,Arial,sans-serif;line-height:1.5;letter-spacing:normal;text-decoration:none;vertical-align:baseline}.error-info{display:flex;flex-flow:wrap;align-items:baseline}.error-panel{padding:1em 0}.error-number,.error-type{font-size:4rem;font-weight:300;margin-right:1rem;word-break:break-all;word-wrap:break-word}.error-items{display:flex;flex-flow:wrap}.error-item{font-size:1rem;margin:.25rem;color:#333;font-weight:300}.error-message{font-size:2rem;color:#999;font-weight:300}.message{color:#999;font-weight:300}.error-code{margin:1em 0 2em}.footer{display:flex;align-items:center;justify-content:center;border-top:1px #ebebeb solid;padding:2em;margin-top:1em;flex-flow:wrap;color:#999}.footer-item{padding:.3rem}.footer a{color:#999;text-decoration:none}@media (min-width:768px){.error-wrapper{width:708px}}@media (min-width:992px){.error-wrapper{width:708px}}@media (min-width:1200px){.error-wrapper{width:960px}}
  </style>
</head>
<body>
  <div class="error-wrapper">
    <div class="error-panel">
      <div class="error-info">
        <div class="error-type"> <?php echo $error_type; ?>  </div>
        <?php if (!is_null($error_code)) : ?>
        <div class="error-number"> <?php echo $error_code; ?></div>
        <?php endif; ?>
        <div class="error-item"><?php echo date('Y-m-d H:i:s e'); ?></div>
        </div>
      </div>
      <div class="error-message"><?php echo $error_message; ?></div>
    
    <div class="footer">
      <?php if (defined('SUDA_START_TIME') &&  defined('SUDA_START_MEMORY')): ?>
      <?php
      $mem =memory_get_usage() - SUDA_START_MEMORY ;
    $human= array('B', 'KB', 'MB', 'GB', 'TB');
    $pos= 0;
    while ($mem >= 1024) {
        $mem /= 1024;
        $pos++;
    }
    $memory_usage  = round($mem, 5) .' '. $human[$pos]; ?>

      <div class="footer-item">Memory Cost: <?php echo $memory_usage; ?></div>
      <div class="footer-item">Time Cost: <?php echo number_format(microtime(true) - SUDA_START_TIME, 5); ?>s</div>

      <?php endif; ?>
      <div class="footer-item">
        <?php if (defined('SUDA_VERSION')): ?>
        <a href="https://github.com/DXkite/suda" target="_black"> <?php echo 'Performance By Suda v'.SUDA_VERSION; ?></a>
        <?php else: ?>
        <a href="https://github.com/DXkite/suda" target="_black"> <?php echo 'Performance By Suda' ?></a>
        <?php endif?>
      </div>

    </div>
  </div>
</body>

</html>