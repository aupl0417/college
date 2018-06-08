<?php
setcookie( 'loginToken', NULL, time() - 1, '/', $_SERVER[ 'HTTP_HOST' ] );
header( 'Location:login.php' );