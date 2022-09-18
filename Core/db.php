<?php

namespace Core ; 

use PDOException;
use PDO;


try {
    $db = new PDO('mysql:host=localhost;dbname=persistent', 'sess_admin', 'ronbetelges');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = $e->getMessage();
}

if (isset($db)) {
    echo 'Connected!';
} elseif (isset($error)) {
    echo $error;
} else {
    echo 'Unknown Error!';
}