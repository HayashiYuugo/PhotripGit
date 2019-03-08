<?php
session_start();//セッションの開始

//DB接続
require('db/dbconnect.php');

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

//コミュニティ投稿機能
if(!empty($_POST)) {//投稿するボタンがクリックされたとき
$image = date('YmdHis') . $_FILES['image']['name'];//画像を時間に変換して格納
move_uploaded_file($_FILES['image']['tmp_name'], 'registrationimage/thumimg/' . $image);//関数でディレクトリのthumimgに画像を格納し、アップロード
$com['info'] = $_POST;//$com['join]に$_POSTを格納
$com['info']['thumimg'] = $image;//アップロードした画像を変数に格納

$insertcom = $db->prepare("INSERT INTO community SET member_id=?,comtitle=?,thumimg=?,numrest=?,date=?,location=?,subject=?,use_camera=?,comment=?,created=NOW()");
$insertcom->execute(array(
  //格納するカラム
  $member['id'],
  $com['info']['comtitle'],
  $com['info']['thumimg'],
  $com['info']['numrest'],
  $com['info']['date'],
  $com['info']['location'],
  $com['info']['subject'],
  $com['info']['use_camera'],
  $com['info']['comment'],
));
header('Location: index.php');
exit();
}

//ユーザーが入力した使用カメラを変換する為の配列
$camera = [
	0 => 'デジタル一眼レフカメラ',
	1 => 'スマートフォン',
	2 => 'ミラーレス一眼カメラ',
	3 => 'コンパクトデジタルカメラ',
	4 => 'ドローン',
	5 => 'ビデオカメラ'
];

//コミュニティ投稿表示機能
$community = $db->query('SELECT m.name,m.picture, c. * FROM members
m, community c WHERE m.id=c.member_id ORDER BY created DESC');


//検索結果用の配列
$row = [];

//検索機能
if(!empty($_GET['serch'])) {
	//検索機能のSQL 
	$keyword = $_GET['serch'];
	//コミュニティのcomtitle,location,subject,members.nameで検索できる
$sql = "SELECT community.id,community.comtitle,community.thumimg,community.location,community.date,community.numrest, members.name,members.picture FROM community RIGHT OUTER JOIN members ON community.member_id = members.id WHERE community.comtitle LIKE '%$keyword%' OR community.location LIKE '%$keyword%' OR community.subject LIKE '%$keyword%' OR members.name LIKE '%$keyword%'";
$stmt = $db->query($sql);
$rowcount = $stmt->rowCount();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>Photrip/コミュニティ</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"><!--bootstrapのリンク-->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"><!--FontAwesomeのリンク-->
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--h1のgoogleフォントリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/slidebars.min.css"><!--index.htmlのcssリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/Slidebar.css"><!--slidebar.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/collapser/collapser.css"><!--collapser.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/com_header.css"><!--headerのcssリンク-->
	<link rel="stylesheet" type="text/css" href="../css/index.css"><!--index.htmlのcssリンク-->
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans+SC:100" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans+SC:300,400" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=M+PLUS+1p:300" rel="stylesheet">
</head>
<body>

	<div canvas="container"><!--containerエリアの開始-->
		<div id="wrapper"><!--wrapperエリアの開始-->
			<div id="header"><!--headerエリアの開始-->
				<div id="header_title"><!--header_titleエリアの開始-->
					<h1><span>Phot</span>rip</h1>
				</div><!--header_titleエリアの終了-->
				<div id="header_nav"><!--header_navエリアの開始-->
					<dl>
						<div class="Photo">
							<dt><a href="Photo.php"><i class="fas fa-camera-retro"></i></a></dt>
							<dd>写真</dd>
						</div>
						<div class="Notification">
							<dt><i class="far fa-bell"></i></dt>
							<dd>通知</dd>
						</div>
						<div class="Upload">
							<dt><i class="fas fa-cloud-upload-alt" id="uploadbtn"></i></dt>
							<dd>投稿</dd>		
						</div>
						<div class="Menu">
							<dt><i class="fas fa-bars menubtn"></i></dt>
							<dd>メニュー</dd>
						</div>	
					</dl>
				</div><!--header_navエリアの終了-->
			</div><!--heaerエリアの終了-->

			<div id="header_after_image"><!--header_after_imageエリアの開始-->
				<p></p>
			</div><!--header_afterエリアの終了-->

			<p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="profile_image"></p>

			<form action="index.php" method="GET"><!--コミュニティ検索機能エリアのform-->
				<div id="serch-input"><!--serch-inputエリアの開始-->
					<input type="search" class="form-control" name="serch" id="serch"  placeholder="キーワード、地名">
					<input type="submit" class="btn btn-primary" value="検索">
				</div><!--serch-inputエリアの終了-->
			</form>

			<?php if(!empty($row)) {?>
			<div id="serch_count"><!--serch-countエリアの開始-->
				<p>検索結果<?php print(htmlspecialchars($rowcount,ENT_QUOTES));?>件..</p>
			</div><!--serch-inputエリアの終了--->
			<?php }?>

			<div id="serch_result"><!--serch_resultエリアの開始-->
			<?php foreach($row as $result){ 
					$str = $result['date'];
					$dt = date('Y年m月d日', strtotime($str));?>
				<div class="comdisplay"><!--comdisplayエリアの開始-->
					<div id="com_left"><!--com_leftエリアの開始-->
						<p><img src="registrationimage/thumimg/<?php print(htmlspecialchars($result['thumimg'], ENT_QUOTES));?>" width="200" height="170"></p>
					</div><!--com_leftエリアの終了-->
					<div id="com_right"><!--com_rightエリアの開始-->
						<h2><?php print(htmlspecialchars($result['comtitle'], ENT_QUOTES)); ?></h2>
						<dl>
							<dt>撮影場所 :</dt>
							<dd><?php print(htmlspecialchars($result['location'], ENT_QUOTES)); ?></dd>
							<dt>撮影時間 :</dt>
							<dd><?php print(htmlspecialchars($dt, ENT_QUOTES)); ?></dd>
							<dt>人数 :</dt>
							<dd><?php print(htmlspecialchars($result['numrest'], ENT_QUOTES)); ?>人まで</dd>
						</dl>
						<button type="button" class="btn btn-success"><a href="com_detail.php?id=<?php print(htmlspecialchars($result['id'], ENT_QUOTES)); ?>">詳細を見る</a></button>
						<p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($result['picture'], ENT_QUOTES)); ?>" width="60" height="60"></p>
					</div><!--com_rihgtエリアの終了-->
				</div><!--comdispalyエリアの終了-->
			<?php }?>
			</div><!--serch_resultエリアの終了-->


			<div id="maincontents"><!--maincontentsエリアの開始-->
			<?php foreach($community as $com){
			$str = $com['date'];
			$dt = date('Y年m月d日', strtotime($str));?>

				<div class="comdisplay"><!--comdisplayエリアの開始-->
					<div id="com_left"><!--com_leftエリアの開始-->
						<p><img src="registrationimage/thumimg/<?php print(htmlspecialchars($com['thumimg'], ENT_QUOTES));?>" width="200" height="170"></p>
					</div><!--com_leftエリアの終了-->
					<div id="com_right"><!---com_rightエリアの開始-->
						<h2><?php print(htmlspecialchars($com['comtitle'], ENT_QUOTES)); ?></h2>
						<dl>
							<dt>撮影場所 :</dt>
							<dd><?php print(htmlspecialchars($com['location'], ENT_QUOTES)); ?></dd>
							<dt>撮影時間 :</dt>
							<dd><?php print(htmlspecialchars($dt, ENT_QUOTES)); ?></dd>
							<dt>人数 :</dt>
							<dd><?php print(htmlspecialchars($com['numrest'], ENT_QUOTES)); ?>人まで</dd>
						</dl>
						
						<button type="button" class="btn btn-success"><a href="com_detail.php?id=<?php print(htmlspecialchars($com['id'], ENT_QUOTES)); ?>">詳細を見る</a></button>
						<p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($com['picture'], ENT_QUOTES)); ?>" width="60" height="60"></p>
					</div><!--com_rightエリアの終了-->
				</div><!--comdisplayエリアの終了-->
			<?php } ?>
			</div><!--maincotnentsエリアの終了-->
		</div><!--wrapperエリアの終了-->
	</div><!--containerエリアの終了-->

	<div off-canvas="sb-right right push"><!--sb-rightエリアの開始-->
		<div id="slidecontents"><!--slidecontentsエリアの開始-->
			<div id="slidecont_header"><!--sllidecont_headerエリアの開始-->
				<ul>
						<li class="slidecont_head_icon"><i class="far fa-user-circle fa-lg"></i></li>
						<li class="slidecont_head_icon"><i class="fas fa-camera fa-lg camera"></i></li>
					<div id="slidecont_head_subtitle"><!--slidecont_head_subtitleエリアの開始-->
						<li><a href="index.php">コミュニティ</a></li>
						<li><a href="Photo.php">写真</a></li>
					</div><!--slidecont_head_subtitleエリアの終了-->
				</ul>
			</div><!---slidecont_headerエリアの終了-->
			<div id="slidecont_main"><!--slidecont_mainエリアの開始-->
					<ul>
						<li class="slidecont_icon"><a href="index.php">トップ</a></li>
						<li class="slidecont_icon"><a href="mypage.php">マイページ</a></li>
						<li class="slidecont_icon"><a href="login/logout.php">ログアウト</a></li>
					</ul>
			</div><!--slidecont_mainエリアの終了-->
		</div><!--slidecontentsエリアの終了-->
	</div><!--sb-rightエリアの終了-->


	<!--popup表示エリア-->
	<div id="image_post_screen"><!--com_post_screenエリアの開始-->

		<h2>コミュニティ作成</h2>
		<form action="" method="POST" enctype="multipart/form-data">

			<div id="com_post_left"><!--com_post_leftエリアの開始-->
				<div class="form-group"><!--コミュニティ名-->
					<label for="comtitle"><span class="beforeicon1">コミュニティ名</span></label>
					<input type="name" class="form-control" name="comtitle" placeholder="コミュニティ名" required>
				</div>
				<div class="form-group"><!--サムネイル画像-->
					<label for="thumimg" class="file"><span class="beforeicon2">サムネイル画像</span></label><br>
					<input type="file" class="form-control mb-2" id="thumimg" name="image" required>
					<div class="imagepre"></div>
				</div>
				<div class="form-group"><!--人数-->
					<label for="comrest"><span class="beforeicon3">人数</span></label>
					<input type="number" class="form-control" step="1" min="0" max="20" name="numrest" placeholder="人数を入力" required>
				</div>
			</div><!--com_post_leftエリアの終了-->

			<div id="com_post_right"><!--com_post_rightエリアの開始-->
				<div class="form-group"><!--日時-->
					<label for="date"><span class="beforeicon4">日時</span></label>
					<input type="date" class="form-control" name="date" placeholder="日時を入力" required>
				</div>
				<div class="form-group"><!--撮影地-->
					<label for="location"><span class="beforeicon5">撮影地</span></label>
					<input type="name" class="form-control" name="location" placeholder="主な撮影地を入力" required>
				</div>
				<div class="form-group"><!--主な被写体-->
					<label for="subject"><span class="beforeicon6">主な被写体</span></label>
					<input type="name" class="form-control" name="subject" placeholder="主な被写体を入力" required>
				</div>
				<div class="form-group"><!--主な使用カメラ-->
					<label for="use_camera"><span class="beforeicon7">主な使用カメラ</span></label>
						<select class="form-control" name="use_camera" required>
							<option value="0">デジタル一眼レフカメラ</option>
							<option value="1">スマートフォン</option>
							<option value="2">ミラーレス一眼レフカメラ</option>
							<option value="3">コンパクトデジタルカメラ</option>
							<option value="4">ドローン</option>
							<option value="5">ビデオカメラ</option>
						</select>
				</div>
				<div class="form-group"><!--作成者コメント-->
					<label for="comment"><span class="beforeicon8">作成者コメント</span></label>
					<textarea class="form-control" rows="3" name="comment" required></textarea>
				</div>
				<button type="submit" class="btn-primary mb-5 p-2 h5 rounded mt-3">作成</button>
			</div><!--com_post_rightエリアの終了-->
		</form>
	</div><!--com_post_screenエリアの終了-->

	
<script type="text/javascript" src="../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebars.min.js"></script><!--slidebarのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebar.js"></script><!--slidebarの相対リンク-->
<script type="text/javascript" src="../js/collapser/jquery.collapser.min.js"></script><!--collapserのjQueryリンク-->
<script type="text/javascript" src="../js/collapser/collapser.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/bpopup/jquery.bpopup.min.js"></script><!--bpopupのリンク-->
<script type="text/javascript" src="../js/bpopup/bpopup.js"></script><!--bpopupの相対リンク-->
<script type="text/javascript" src="../js/imagepreview/imagepreview.js"></script><!--bpopupの相対リンク-->
<script type="text/javascript" src="../js/pointer.js"></script><!--pointer.jsの相対リンク-->
<script type="text/javascript" src="../js/changeDateHeading/changeDateHeading.js"></script><!--changeDateHeading.jsの相対リンク-->

</body>
</html>