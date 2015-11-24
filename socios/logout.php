<?php session_start() ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Videoclub</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        session_destroy();
        setcookie(session_name(), '', 1);
        header("Location: login.php"); ?>
    </body>
</html>

