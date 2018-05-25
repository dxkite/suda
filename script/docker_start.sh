## start docker suda system

if [ ! -f "/app/manifast.json" ];then
chmod a+rw /app
echo 'create default app in ~/app'
fi

mkdir -p /app/data/runtime
chmod a+rw -R app/data
echo '<?php return ["passwd" => ""]; ' > /app/data/runtime/database.config.php

lampp startmysql
lampp startapache