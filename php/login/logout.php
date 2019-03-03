<?php
session_start();

$_SESSION = array();
if(ini_set('session.use_cookies')) {
    //クッキーの情報を削除するための処理
    $param = session_get_cookie_param();
    setcookie(session_name() . '', time() - 42000,
    //削除書式  
    $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

//セッションの破棄
session_destroy();

//クッキーの有効期限を切る
setcookie('email', '', time() - 3600);

//ログイン画面へ移動
header('Location: login.php');
exit();

?>