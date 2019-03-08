<?php

session_start();

require('../db/dbconnect.php');//DBファイルへの接続

if($_COOKIE['email'] !== '') {//クッキーの中のメールアドレスが空でなければ
  $email = $_COOKIE['email'];//クッキーのメールアドレスを$mailに格納
}

if(!empty($_POST)) {//$_POSTの値が空でなければ
  $email = $_POST['email'];
  //メールアドレスとパスワードが空でなければ
  if($_POST['email'] !== '' && $_POST['password'] !== '') {
    $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?');//SQL文
    $login->execute(array(//実行
      //メールアドレスとパスワードを検索
      $_POST['email'],
      sha1($_POST['password'])//パスワードを引っ張る時にsha1で正規化
    ));
    $member = $login->fetch();//検索したデータを一行ごとに取得

    if($member) {//$memberでデータが取得できた場合
      $_SESSION['id'] = $member['id'];//ヒットしたidをセッションに格納
      $_SESSION['time'] = time();//ログインした時の時刻

      if($_POST['save'] === 'on') {
        setcookie('email',$_POST['email'], time()+60*60*24*14);
      } 

      header('Location: ../index.php');//Main.phpに移行
      exit();
    }
    else{
      $error['login'] = 'failed';
    }
  }
}

?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Photrip/ログイン</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"><!--bootstrapのリンク-->
<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--googlefontのリンク-->
<link rel="stylesheet" type="text/css" href="../../css/login/login.css"><!--cssの相対リンク-->
<link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
</head>
<body>

  <div id="wrapper"><!--wrapperエリアの開始-->
      <div id="maincontents"><!--maincontentsエリアの開始-->

        <div id="service_title"><!--service_titleエリアの開始-->
            <h1>Phot<span>rip</span></h1>
        </div><!--service_titleエリアの終了-->

        <?php if($error['login'] === 'failed' ){ ?>
        <p class="error">ログインに失敗しました。正しくご記入ください。</p>
        <?php } ?>

        <form action="" method="POST">
          <div class="form-group"><!--メールアドレスエリア-->
            <input type="mail" class="form-control" id="email" name="email" placeholder="メールアドレス" value="" required>
          </div>
          <div class="form-group"><!--パスワードエリア--->
            <input type="password" class="form-control mb-4" id="password" name="password" placeholder="パスワード" value="" required>
          </div>
          <button type="submit" class="btn-primary mb-5 p-2 h5 rounded mt-3 w-100" id="login">ログイン</button>
        </form>
      </div><!--maincontentsエリアの終了-->

      <div id="subcontents"><!--subcontentsエリアの開始-->
        <p>アカウントをお持ちですか？<a href="../signup/signup.php">新規登録</a>はこちら</p>
      </div><!--subcontentsエリアの終了-->

  </div><!--wrapperエリアの終了-->

<script type="text/javascript" src="../../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../../js/bgswitcher/jquery.bgswitcher.js"></script><!--bgswitcherのリンク-->
<script type="text/javascript" src="../../js/bgswitcher/bgswitcher.js"></script><!--bgswitcherの相対リンク-->

</body>
</html>
