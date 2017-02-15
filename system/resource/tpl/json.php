<? echo $comment ?>

return api_permission('<?php echo addslashes($permission) ?>', function ( $param) {
    <?php echo $interface ?>
});

