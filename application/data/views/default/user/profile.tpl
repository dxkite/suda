<html>
    <head>
        <title><?php echo htmlspecialchars($v->helloworld) ?></title>
    </head>
    <body>
        <div> <?php echo htmlspecialchars(_T($v->helloworld)) ?>@/user/{user:string}/profile </div>
    </body>
</html>