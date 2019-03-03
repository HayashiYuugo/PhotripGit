<?php

//セッションの開始
session_start();

//データベース接続のリンク
require('../db/dbconnect.php');

if(!empty($_POST)) {//$_POSTを送信したとき	



	$fileName = $_FILES['image']['name'];//アップロードされたファイル名を$fileNmaeに格納
	if(!empty($fileName)) {//画像がアップロードされていれば
		$ext = substr($fileName , -3);//画像ファイルの拡張子を検査
		if($ext != 'jpg' && $ext != 'gif' && $ext != 'png' && $ext != 'jpeg') {//拡張子がjpg,gif,png以外の場合は
			$error['image'] = 'type';//エラーを表示。
		}
	}

	//アカウントの重複をチェック
	if(empty($error)) {//これまでエラーがなければ
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');//メールアドレスの件数が何件か取得しする。
		$member->execute(array($_POST['email']));
		$record = $member->fetch();//fetchメソッドで取り出す。
	}	
	if($_POST['password'] !== $_POST['password2']) {
		$error['pass'] = 'same';
	}
	if($record['cnt'] > 0) {//もし、データが0異常なら
		$error['email'] = 'duplicate';//エラーを表示。
	}

	if(empty($error)) {//エラーを全て通過した場合
		$image = date('YmdHis') . $_FILES['image']['name'];
		move_uploaded_file($_FILES['image']['tmp_name'], '../registrationimage/member_picture/' . $image);
		$_SESSION['join'] = $_POST;
		$_SESSION['join']['image'] = $image;
		header('Location: signupconf.php');//ページを遷移
		exit();
	}
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Photrip/新規登録</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"><!--Bootstrapのリンク-->
<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--googlefontのリンク-->
<link rel="stylesheet" type="text/css" href="../../css/signup/signup.css"><!--cssのリンク-->
<link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
</head>
<style>

</style>
<body>
	<div id="wrapper"><!---wrapperエリアの開始-->

		<div id="maincontents"><!--maincotentエリアの開始-->

			<div id="service_title">
				<h1 class="text-center">Phot<span>rip</span></h1>
			</div>


			<?php if($error['email'] === 'duplicate') : ?>
			<p class="emailerror">このメールアドレスはすでに登録されています</p>
			<?php endif;?>
			<?php if($error['pass'] === 'same') : ?>
			<p class="passerror">パスワードが一致しません。正しくご入力ください。</p>
			<?php endif;?>

			<form action="" method="POST" enctype="multipart/form-data">
				<div class="form-group"><!--ユーザーネームエリア-->
					<input type="name" class="form-control" name="name" placeholder="ユーザーネーム" required>
				</div>
				<div class="form-group"><!--メールアドレスエリア-->
					<input type="mail" class="form-control" name="email" class="email" placeholder="メールアドレス" required>
				</div>
				<div class="form-group"><!--パスワードエリア-->
					<input type="password" class="form-control" name="password" id="password" placeholder="パスワード" required>
				</div>
				<div class="form-group"><!--パスワード(確認用)エリア-->
					<input type="password" class="form-control" name="password2" id="password2" placeholder="パスワード(確認用)" required>
				</div>
				<div class="form-group"><!--プロフィール写真エリア-->
					<label for="profileimg">プロフィール写真を選択</label>
						<input type="file" class="form-control-file" name="image" required>
				<div class="imagepre"></div>
				</div>

				<button type="submit" class="btn-primary mb-5 p-2 h5 rounded mt-3 w-100">確認</button>
			</form>

		</div><!--maincontentsエリアの終了-->

		<div id="subcontents"><!--subcontenstエリアの開始-->

			<p>アカウントをお持ちですか？<a href="../login/login.php">ログイン</a>はこちら</p>

		</div><!--subcontentsエリアの終了-->

	</div><!---wrapperエリアの終了-->

<script type="text/javascript" src="../../js/jquery-2.0.2.min.js"></script><!---jqueryのリンク-->
<script type="text/javascript" src="../../js/bgswitcher/jquery.bgswitcher.js"></script><!--bgswithcherのjsリンク-->
<script type="text/javascript" src="../../js/bgswitcher/bgswitcher.js"></script><!--bgswithcherの相対jsリンク-->
<script type="text/javascript" src="../../js/imagepreview/imagepreview.js"></script><!--imagepreviewの相対jsリンク-->
<script type="text/javascript" src="../../js/email-auto/jquery.email-autocomplete.js"></script><!--imagepreviewの相対jsリンク-->
<script>
$(function(){
	


});
		
</script>
</body>
</html>