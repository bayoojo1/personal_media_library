<?php
try {
    $db_connect = new PDO('mysql:host=localhost;dbname=media_library', 'root', 'wifi1234');
    $db_connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage()."<br>";
    die();
}
?>
