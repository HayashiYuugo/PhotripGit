<?php

session_start();//セッションの開始

require('db/dbconnect.php');//DBに接続
//ログイン処理(パーツ化したいがエラーが出る)
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


//コミュニティid格納
$cominfo = 'SELECT id FROM community';
$stmt = $db->query($cominfo);
if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$comid = $row['id'];
}


//写真投稿画面
if(!empty($_POST)) {//投稿するボタンがクリックされたとき
	$image = date('YmdHis') . $_FILES['image']['name'];//画像データを時刻に変換
	move_uploaded_file($_FILES['image']['tmp_name'], 'registrationimage/photoimg/' . $image);//ファイルのアップロード
	$photo['join'] = $_POST;//$photo['join]に格納
	$photo['join']['photoimg'] = $image;
	
	$insertphoto = $db->prepare("INSERT INTO photoposts SET member_id=?,community_id=?,photoimg=?,photolocation=?,photostory=?,belongscom=?,use_camera=?,use_lens=?,created=NOW()");
	//SQL文を実行
	$insertphoto->execute(array(
		$member['id'],
		$photo['join']['belongscom'],
		$photo['join']['photoimg'],//投稿写真
		$photo['join']['photolocation'],//撮影場所
		$photo['join']['photostory'],//ストーリー(写真にまつわるストーリー)
		$comid,//所属コミュニティ
		$photo['join']['use_camera'],//使用カメラ
		$photo['join']['use_lens']//使用レンズ
	));
	header('Location: Photo.php');
	exit();
}

//写真投稿表示をする為の処理
//写真作成内容をDBからSELECTで取得
$photos = $db->query('SELECT m.name,m.picture, p. * FROM members
m, photoposts p WHERE m.id=p.member_id ORDER BY created DESC'); 


//コメント数をPhoto.phpで表示するための処理
$sql = "SELECT photoposts_id , COUNT(*) AS comment FROM reply_comment GROUP BY photoposts_id";
$stmt = $db->query($sql);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$photovalue = $row['comment'];
	$photoid = $row['photoposts_id'];
	$idcom[$photoid] = $photovalue;
}

// 検索機能
// 検索結果ようにからの配列を用意する
$row = [];

if(!empty($_GET['serch'])) {
	//検索機能のSQL 
	$keyword = $_GET['serch'];
	// var_dump($keyword);
	//コミュニティのcomtitle,location,subject,members.nameで検索できる
$sql = "SELECT photoposts.id,photoposts.photoimg,photoposts.photolocation,members.name,members.picture FROM photoposts RIGHT OUTER JOIN members ON photoposts.member_id = members.id WHERE photoposts.photolocation LIKE '%$keyword%' OR members.name LIKE '%$keyword%' OR photoposts.photostory LIKE '%$keyword%' OR photoposts.use_camera LIKE '%$keyword%' OR photoposts.use_lens LIKE '%$keyword%'";
$stmt = $db->query($sql);
$rowcount = $stmt->rowCount();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

//写真投稿時にコミュニティ名を表示する処理
$memEntryrow = [];
$memCommunity = 'SELECT community.id,community.comtitle FROM community INNER JOIN member_community ON community.id = member_community.community_id WHERE member_community.member_id = '.$member['id'];
$stmt = $db->query($memCommunity);
$memEntryrow = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>Photrip/写真</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"><!--bootstrapのリンク-->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"><!--FontAwesomeのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/slidebars.min.css"><!--slidebar.jsのjsリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/Slidebar.css"><!--slidebar.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/collapser/collapser.css"><!--collapser.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/photo.css"><!--photo.cssの相対リンク-->
	<link rel="stylesheet" type="text/css" href="../css/photo_header.css"><!--headerのcssリンク-->
	<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--h1のgoogleフォントリンク-->
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans+SC:100" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet"> 
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans+SC:300,400" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=M+PLUS+1p:300" rel="stylesheet">
</head>
<body>
	<div canvas="container"><!--containerエリアの開始-->
		<div id="wrapper"><!--wrapperエリアの開始-->
				<div id="header"><!--headerエリアの開始-->
					<div id="header_title">
						<h1><span>Phot</span>rip</h1>
					</div>
					<div id="header_nav"><!--header_navエリアの開始-->
						<dl>
							<div class="Community">
								<dt><a href="index.php"><i class="fas fa-user-friends"></i></a></dt>
								<dd>コミュニティ</dd>
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
					</div><!--header_navエリアの終了-->
				</div><!--heaerエリアの終了-->

				<div id="header_after_image"><!--header_after_imageエリアの開始-->
						<p></p>
				</div><!--header_afterエリアの終了-->

				<p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="profile_image"></p>

				<form action="Photo.php" method="GET"><!--コミュニティ検索機能エリアのform-->
					<div id="serch-input"><!--serch-inputエリアの開始-->
						<input type="search" class="form-control" name="serch" id="serch"  placeholder="キーワード、地名">
						<input type="submit" class="btn btn-primary" value="検索">
					</div><!--serch-inputエリアの終了-->
				</form>

				<?php if(!empty($row)) {?>
				<div id="serch_count"><!--serch-countエリアの開始-->
					<p　id="serch_count">検索結果<?php print(htmlspecialchars($rowcount,ENT_QUOTES));?>件..</p>
				</div><!--serch-countエリアの終了-->
				<?php }?>

				<div id="serch_result"><!--serch_resultの開始エリア-->
				<?php foreach($row as $result) {?>
						<div class="photodisplay"><!--photodisplayエリアの開始-->
							<h2><a href="photo_detail.php?id=<?php print(htmlspecialchars($result['id'], ENT_QUOTES)); ?>"><img src="registrationimage/photoimg/<?php print(htmlspecialchars($result['photoimg'], ENT_QUOTES)); ?>" width="380" height="285"></h2>
							<div class="mask"><!--maskエリアの開始-->
								<div id="mask_header" class="text-white"><!--mask_headerエリアの開始-->
									<p><?php print(htmlspecialchars($result['photolocation'], ENT_QUOTES)); ?></p>
								</div><!--mask_headerエリアの終了-->
								<div id="mask_profile" class="text-white"><!--mask_profileエリア開始了-->
									<ul>
										<li><img src="registrationimage/member_picture/<?php print(htmlspecialchars($result['picture'], ENT_QUOTES)); ?>" width="30" height="30"><li>
										<li><?php print(htmlspecialchars($result['name'], ENT_QUOTES)); ?></li> 
									</ul>
								</div><!--mask_profielエリアの終了-->
								<div id="mask_footer"><!--mask_footerエリアの開始-->
									<ul>
										<li><i class="icon_color far fa-heart fa-lg"></i></li>
										<p id="count_comment"><?= $idcom[$result['id']]; ?></p>
										<li><i class="icon_color far fa-comment-alt fa-lg"></i></li>
									</ul>
								</div><!--mask_fotterエリアの終了-->
							</div><!--maskエリアの終了-->
						</div><!--photodisplay-エリアの終了-->
				<?php }?>
				</div><!--serch_resultの終了エリア-->

			<div id="maincontents"><!--maincontentsエリアの開始-->
			<?php foreach ($photos as $photo):?>
					<div class="photodisplay"><!--photodisplayエリアの開始-->
						<h2><a href="photo_detail.php?id=<?php print(htmlspecialchars($photo['id'], ENT_QUOTES)); ?>"><img src="registrationimage/photoimg/<?php print(htmlspecialchars($photo['photoimg'], ENT_QUOTES)); ?>" width="380" height="285"></h2>
						<div class="mask"><!--maskエリアの開始-->
							<div id="mask_header" class="text-white"><!--mask_headerエリアの開始-->
								<p><?php print(htmlspecialchars($photo['photolocation'], ENT_QUOTES)); ?></p>
							</div><!--mask_headerエリアの終了-->
							<div id="mask_profile" class="text-white"><!--mask_profileエリア開始了-->
								<ul>
									<li><img src="registrationimage/member_picture/<?php print(htmlspecialchars($photo['picture'], ENT_QUOTES)); ?>" width="30" height="30"><li>
									<li><?php print(htmlspecialchars($photo['name'], ENT_QUOTES)); ?></li> 
								</ul>
							</div><!--mask_profielエリアの終了-->
							<div id="mask_footer"><!--mask_footerエリアの開始-->
								<ul>
									<li><i class="icon_color far fa-heart fa-lg"></i></li>
									<p id="count_comment"><?= $idcom[$photo['id']]; ?></p>
									<li><i class="icon_color far fa-comment-alt fa-lg"></i></li>
								</ul>
							</div><!--mask_fotterエリアの終了-->
						</div><!--maskエリアの終了-->
					</div><!--photodisplay-エリアの終了-->
			<?php endforeach; ?>	
			</div><!--maincontentsエリアの終了-->
		</div><!--wrapperエリアの終了-->
	</div><!--containerエリアの終了-->

	<div off-canvas="sb-right right push"><!--sb-rightエリアの開始-->
		<div id="slidecontents"><!--slidecontentsエリアの開始-->
			<div id="slidecont_header"><!--slidecont_headerエリアの開始-->
				<ul>
						<li class="slidecont_head_icon"><i class="far fa-user-circle fa-lg" style="color:white;"></i></li>
						<li class="slidecont_head_icon"><i class="fas fa-camera fa-lg"  style="color:white; "></i></li>
					<div id="slidecont_head_subtitle"><!---slidecont_head_subtitleエリアの開始-->
						<li class="link_color"><a href="index.php">コミュニティ</a></li>
						<li class="link_color"><a href="Photo.php">写真</a></li>
					</div><!--slidecont_head_subtitleエリアの終了-->
				</ul>
			</div><!--slidecont_headerエリアの終了-->
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
	<div id="image_post_screen"><!--image_post_screenエリアの開始-->
	<h2>写真投稿</h2>
	<form action="" method="POST" enctype="multipart/form-data">

	<div id="image_post_left"><!--image_post_leftエリアの開始-->
		<div class="form-group"><!--投稿写真エリア-->
			<label for="photoimg"><span class="beforeicon1">画像をアップロードする</span></label>
			<input type="file" class="form-control" name="image" placeholder="写真をおえらびください" required>
			<div class="imagepre"></div>
		</div>
		<div class="form-group"><!--撮影場所エリア-->
			<label for="photolocation"><span class="beforeicon2">撮影場所</span></label>
			<input type="name" class="form-control" name="photolocation" placeholder="場所、地名" required>
		</div>
	</div><!--image_post_leftエリアの終了-->

		<div id="image_post_right"><!--iamge_post_rightエリアの開始-->
			<div class="form-group">
				<label for="belongscom"><span class="beforeicon3">コミュニティを選択</span></label>
				<select class="form-control" id="exampleFormControlSelect1" name="belongscom">
			<?php foreach($memEntryrow as $mementry){?>
					<option value="<?php print(htmlspecialchars($mementry['id'],ENT_QUOTES));?>"><?php print(htmlspecialchars($mementry['comtitle'],ENT_QUOTES)); ?></option>
			<?php } ?>
				</select>
			</div>
			<div class="form-group"><!--使用カメラエリア-->
				<label for="use_camera"><span class="beforeicon4">使用カメラ</span></label>
				<input type="name" class="form-control" name="use_camera" placeholder="使用カメラをお書きください(詳しく)" required>
			</div>
			<div class="form-group"><!--使用レンズエリア-->
				<label for="use_lens"><span class="beforeicon4">使用レンズ</span></label>
				<input type="name" class="form-control" name="use_lens" placeholder="使用レンズをお書きください(詳しく)" required>
			</div>
			<div class="form-group"><!--ストーリーエリア-->
				<label for="photostory"><span class="beforeicon5">ストーリー</span></label>
				<textarea class="form-control" rows="3" name="photostory" placeholder="写真にまつわるストーリーをお書きください" required></textarea>
			</div>
			<button type="submit" class="btn-primary mb-5 p-2 h5 rounded mt-3">投稿</button>
		</div><!--image_post_rihgtエリアの終了	-->
		</form>
	</div><!--image_post_screenエリアの終了-->

	<div class="form-group"><!--所属コミュニティエリア-->
		<label for="belongscom"><span class="beforeicon3">所属コミュニティ</span></label>
		<input type="name" class="form-control" name="belongscom" placeholder="所属コミュニティをお書きください" required>
	</div>

		
<script type="text/javascript" src="../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebars.min.js"></script><!--slidebarのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebar.js"></script><!--slidebarの相対リンク-->
<script type="text/javascript" src="../js/bpopup/jquery.bpopup.min.js"></script><!--bpopupのリンク-->
<script type="text/javascript" src="../js/bpopup/photo_bpopup.js"></script><!--bpopupの相対リンク-->
<script type="text/javascript" src="../js/imagepreview/imagepreview.js"></script><!--imagepreviewの相対リンク-->
<script type="text/javascript" src="../js/collapser/jquery.collapser.min.js"></script><!--collapserのjQueryリンク-->
<script type="text/javascript" src="../js/collapser/collapser.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/changeDateHeading/changeDateHeading.js"></script><!--changeDateHeading.jsの相対リンク-->

</body>
</html>





