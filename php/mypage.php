<?php 

session_start();

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


//自分の投稿内容やコミュニティ作成内容を取得
$coms = $db->prepare('SELECT m.name,m.picture, c.* FROM members
m, community c WHERE m.id=c.member_id AND c.id=?'); 

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Photrip/マイページ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"><!--bootstrapのリンク-->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"><!--FontAwesomeのリンク-->
	<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--h1のgoogleフォントリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/slidebars.min.css"><!--index.htmlのcssリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/Slidebar.css"><!--slidebar.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/collapser/collapser.css"><!--collapser.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/com_header.css"><!--headerのcssリンク-->
  <link rel="stylesheet" type="text/css" href="../css/mypage.css"><!--index.htmlのcssリンク-->
  <link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
</head>
<body>
  <div canvas="container"><!--containerエリアの開始-->
  <div id="header"><!--headerエリアの開始-->
			<h1><span>Phot</span>rip</h1>
			<div id="header_nav"><!--header_navエリアの開始-->
      <ul>
					<li><span class="photo_span">Photo</span><a href="Photo.php"><i class="fas fa-camera-retro list-item"></i></a></li>
					<li><span class="photo_notification">Notification</span><i class="far fa-bell fa-lg list-item"></i></li>
					<li><span class="photo_upload">Upload</span><i class="fas fa-cloud-upload-alt list-item" id="uploadbtn"></i></li>
					<li><span class="photo_menu">Menu</span><i class="fas fa-bars fa-lg list-item menubtn"></i></li>
				</ul>
			</div><!--header_navエリアの終了-->
		</div><!--heaerエリアの終了-->

    <div id="profile_area">
    <h2><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="my_icon"></h2>

    <p><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?></p>
    </div>

    <div id="maincontents"><!--maincontentsエリアの開始-->
    <ul>
      <li><a href="#">写真</a><li>
      <li><a href="#">投稿コミュニティ</a></li>
      <li><a href="#">参加コミュニティ</a></li>
      <li><a href="#">いいね</a></li>
      <li><a href="#">コメント</a></li>
    </ul>

    <div id="main_result"></div>
    </div><!--maincontentsエリアの終了-->
  </div><!--containerエリアの終了-->

<script type="text/javascript" src="../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebars.min.js"></script><!--slidebarのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebar.js"></script><!--slidebarの相対リンク-->
<script type="text/javascript" src="../js/bpopup/jquery.bpopup.min.js"></script><!--bpopupのリンク-->
<script type="text/javascript" src="../js/bpopup/bpopup.js"></script><!--bpopupの相対リンク-->
<script type="text/javascript" src="../js/imagepreview/imagepreview.js"></script><!--imagepreviewの相対リンク-->
<script type="text/javascript" src="../js/collapser/jquery.collapser.min.js"></script><!--collapserのjQueryリンク-->
<script type="text/javascript" src="../js/collapser/collapser.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/bpopup/jquery.bpopup.min.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/bgswitcher/jquery.bgswitcher.js"></script><!--bpopupのリンク-->
<script>
 
</script>
</body>
</html>