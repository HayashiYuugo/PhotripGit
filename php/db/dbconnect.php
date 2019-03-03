<?php
try {
    $db = new PDO('mysql:dbname=PhotoSV;host=127.0.0.1;charset=utf8','PhotoSV_User','photopost');
}
catch(PDOException $ex){
    print('DB接続エラー:' . $ex->getMessage());
}
?>