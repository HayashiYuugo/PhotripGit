<?php
//ログイン処理
//最後の行動から、1時間ログインが有効
if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
  $_SESSION['time'] = time();//現在の時刻を上書き

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();//取得したデータを$memberに格納
}
else{//最後の行動から1時間以内に行動がなければ
  header('Location: login/login.php');//ログイン画面に戻る
  exit();
}
?>