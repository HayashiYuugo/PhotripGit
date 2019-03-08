<?php

session_start();

require('../db/dbconnect.php');

if(!isset($_SESSION['join'])) {//index.phpから値が渡ってきていない時に
	header('Location: signup.php');//signup.phpへ戻る
	exit();
}

if(!empty($_POST)) {//$_POSTから値が渡ってきている時に
	$statement = $db->prepare('INSERT INTO members SET name=?, email=?, password=?, picture=?, created=NOW()');//DBに格納
	print $statement->execute(array(
		$_SESSION['join']['name'],//名前を格納
		$_SESSION['join']['email'],//メールアドレスを格納
		sha1($_SESSION['join']['password']),//sha1でパスワードを暗号化//パスワードを格納
		$_SESSION['join']['image'],//画像を格納
	));
	unset($_SESSION['join']);//unsetでセッション変数を空にする//トラブルを防ぐため、情報を格納したので使い終わったらすぐに削除

	header('Location: thanks.php');
	exit();

}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Photrip/登録情報確認</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"><!--bootstrapのリンク-->
<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--googlefontのリンク-->
<link rel="stylesheet" type="text/css" href="../../css/signup/signupconf.css"><!---cssの相対リンク-->
<link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
</head>
<body>
	<div id="wrapper"><!--wrapperエリアの開始-->
		<div id="maincontents"><!--maincontentsエリアの開始-->
			<div id="service_title"><!--service_titleエリアの開始-->
				<h1>Phot<span>rip</span></h1>
			</div><!--service_titleエリアの終了-->
			<form action="" method="POST">
				<input type="hidden" name="action" value="submit">
				<dl>
					<dt>ユーザーネーム</dt>
					<dd><?= htmlspecialchars($_SESSION['join']['name'],ENT_QUOTES); ?></dd>
					<dt>メールアドレス</dt>
					<dd><?= htmlspecialchars($_SESSION['join']['email'],ENT_QUOTES); ?></dd>
					<dt>パスワード</dt>
					<dd>表示されません。</dd>
					<dt>プロフィール写真</dt>
					<dd id="imagepre">
					<?php if($_SESSION['join']['image'] !== ''){ ?>
						<img src="../registrationimage/member_picture/<?= (htmlspecialchars($_SESSION['join']['image'], ENT_QUOTES));?> " width="150" height="150">
					<?php } ?>
					</dd>
				</dl>
				<button type="submit" class="btn-primary mb-5 p-2 h5 rounded mt-3 w-100" id="signup">登録</button>
			</form>
		</div><!--maincontentsエリアの終了-->
	</div><!--wrapperエリアの終了-->

<script type="text/javascript" src="../../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../../js/bgswitcher/jquery.bgswitcher.js"></script><!--bgswitcherのリンク-->
<script type="text/javascript" src="../../js/bgswitcher/bgswitcher.js"></script><!--bgswitcherの相対リンク-->

</body>
</html>