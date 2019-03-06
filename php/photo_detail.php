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


//写真投稿のidを取得
$sth = $db->prepare('SELECT * FROM photoposts WHERE id=?');
$sth->execute(
  array($_GET['id'])//URLから何番目の投稿かを判断
);
$photo_id = $sth->fetchAll();//全ての結果を取得

/*コメント投稿機能 */
if(!empty($_POST['comment'])) {/*コメントエリアが空でなければ */
  $reply = $_POST;
  $insertreply = $db->prepare(" INSERT INTO reply_comment (members_id,photoposts_id,comment,created) VALUE (?,?,?,NOW())");
  $insertreply->execute(array(
  $member['id'],
  $_GET['id'],
  $reply['comment']
));

header('Location: photo_detail.php?id='.$_GET['id']);
exit();

}

//写真投稿の詳細を1件表示するための処理 
//写真作成内容をDBからSELECTで取得
$photos = $db->prepare('SELECT m.name,m.picture, p.* FROM members
m, photoposts p WHERE m.id=p.member_id AND p.id=?');
$photos->execute(array($_REQUEST['id']));

//コメントの表示機能
$comments = $db->query('SELECT photoposts.*,reply_comment.*,(SELECT name FROM members WHERE reply_comment.members_id = id) as member_name, (SELECT picture FROM members WHERE reply_comment.members_id = id) as member_picture FROM photoposts,reply_comment WHERE photoposts.id=reply_comment.photoposts_id and photoposts.id=\''.$_REQUEST['id'].'\' ORDER BY reply_comment.id DESC;'); 


//コメントの件数取得
$sql = 'SELECT COUNT(*) AS count_comment FROM reply_comment INNER JOIN photoposts ON reply_comment.photoposts_id = photoposts.id WHERE photoposts.id = \''.$_REQUEST['id'].'\';';
$stmt = $db->query($sql);
if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //コミュニティ人数が$com_member_countに代入
    $count_comment = $row['count_comment'];
}



//写真投稿している人のコミュニティ名を取得する処理
$sql = 'SELECT community.comtitle FROM photoposts INNER JOIN community ON photoposts.community_id = community.id WHERE photoposts.id = \''.$_REQUEST['id'].'\';';
$stmt = $db->query($sql);
if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
  $Comtitle = $row['comtitle'];
}




?>


<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Re:Photo/写真詳細</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"><!--bootstrapのリンク-->
	<link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"><!--FontAwesomeのリンク-->
	<link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--h1のgoogleフォントリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/slidebars.min.css"><!--slidebarsのcssリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/Slidebar.css"><!--slidebar.cssの相対リンク-->
	<link rel="stylesheet" type="text/css" href="../css/collapser/collapser.css"><!--collapser.cssの相対リンク-->
  <link rel="stylesheet" type="text/css" href="../css/photo_detail.css"><!--index.htmlのcssリンク-->
  <link rel="stylesheet" type="text/css" href="../css/photo_header.css"><!--headerのcssリンク-->
  <link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
</head>
<style>

</style>
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
                  <dt><a href="Photo.php"><i class="fas fa-cloud-upload-alt" id="uploadbtn"></i></a></dt>
                  <dd>投稿</dd>		
                </div>
                <div class="Menu">
                  <dt><i class="fas fa-bars menubtn"></i></dt>
                  <dd>メニュー</dd>
                </div>
            </div><!--header_navエリアの終了-->
        </div><!--heaerエリアの終了-->

        <p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="profile_image"></p>

        <div id="maincontents"><!--maincontenetsエリアの開始-->
          <div id="photo_img"><!--photo_imgエリアの開始-->
  <?php if($photo = $photos->fetch()):?>
            <h2><img src="registrationimage/photoimg/<?php print(htmlspecialchars($photo['photoimg'] , ENT_QUOTES)); ?>" width="1277" height="780" id="img" exif="true"></h2>
  <?php endif; ?>
          </div><!---photo_imgエリアの終了-->
        </div><!---maincontentsエリアの終了-->

        <div id="share_list"><!--share_listエリアの開始-->
            <ul>
              <li><i class="share_icon far fa-heart fa-lg"></i></li>
              <li id="count_comment"><?php print($count_comment); ?></li>
              <li><i class="share_icon far fa-comment-alt fa-lg"></i></li>
            </ul> 
        </div><!--share_listエリアの終了-->


      <div id="photo_profile"><!--photo_profileエリアの開始-->
        <h3><img src="registrationimage/member_picture/<?php print(htmlspecialchars($photo['picture'], ENT_QUOTES));?>" width="90" heigth="90"></h3>
        <p id="user_icon"><?php print(htmlspecialchars($photo['name'], ENT_QUOTES)); ?></p>
      </div><!--photo_profileエリアの終了-->
      <div id="photo_location"><!--photo_locationエリアの開始-->
          <p id="location_icon"><?php print(htmlspecialchars($photo['photolocation'] , ENT_QUOTES)); ?></p>
      </div><!--photo_locationエリアの終了-->
      <div id="photo_story"><!--photo_storyエリアの開始--->
          <p><?php print(htmlspecialchars($photo['photostory'] , ENT_QUOTES)); ?></p>
      </div><!--photo_storyエリアの終了-->

      <div id="subcontents"><!--subcontentsエリアの開始-->
        <div id="EXIF_area"><!--EXIF_areaの開始-->
          <h3 id="EXIF_icon">EXIF</h3>
          <div id="EXIF_result"></div><!--EXIF_resultエリア-->
        </div><!---EXIF_areaの終了-->

        <div id="other_info"><!--other_infoエリアの開始-->
          <div id="equipment_area"><!--equipment_areaの開始-->
            <h3 id="equipment_icon">機材</h3>
            <dl>
              <dt>カメラ</dt>
              <dd><?php print(htmlspecialchars($photo['use_camera'], ENT_QUOTES)); ?></dd>
              <dt>レンズ</dt>
              <dd><?php print(htmlspecialchars($photo['use_lens'], ENT_QUOTES)); ?></dd>
            </dl>
          </div><!--equipment_areaの終了--->

          <div id="belongscom_area"><!--belongscom_areaの開始--->
            <h3 id="belongscom_icon">所属コミュニティ</h3>
            <dt>コミュニティ名</dt>
            <dd><?php print(htmlspecialchars($Comtitle));?></dd>
          </div><!--belongscom_areaの終了-->
        </div><!--other_infoエリアの終了-->

        <div id="commentarea"><!--commentareaの開始-->
          <form action="" method="POST">
          <div id="comment_area"><!--comment_areaエリアの開始-->
            <h3 id="comment_icon">コメント</h3>
            <textarea class="form-control" rows="4" placeholder="コメントをお書きください。" name="comment" id="comment"></textarea>
            <button type="submit" class="btn btn-primary">投稿する</button>
          </div><!---comment_areaエリアの終了-->

          <div id="commentcontents"><!--commentcontentsエリアの開始-->

<?php foreach($comments as $comment){
    $str = $comment['created'];
    $dt = date('m月d日 H時i分', strtotime($str));
?>
          <div id="comment_result"><!--comment_resultエリアの開始-->
            <div id="comment_left"><!--comment_leftエリアの開始-->
              <p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($comment['member_picture'], ENT_QUOTES)); ?>" id="comment_picture" width="80" height="60"></p>
            </div><!--comment_leftエリアの終了-->
            <div id="comment_right"><!--comment_rightエリアの開始-->
              <span><?php print(htmlspecialchars($comment['member_name'], ENT_QUOTES)); ?></span>
              <p><?php print(htmlspecialchars($comment['comment'], ENT_QUOTES)); ?></p>
              <p id="comment_date"><?php print(htmlspecialchars($dt, ENT_QUOTES)); ?></p>
            </div><!--comment_rightエリアの終了-->
          </div><!--comment_resultの終了--->


<?php }?>
            
        </div><!--comment_resultエリアの終了-->
        </form>
      </div><!--commentareaの終了-->
    </div><!--subcontentsエリアの終了-->

    <div id="gmap"></div><!----googlemapエリア-->
    </div><!--wrapperエリアの終了-->
  </div><!--containerエリアの終了-->




  <div off-canvas="sb-right right push"><!--slidebarエリアの開始-->
		<div id="slidecontents"><!--slidecontentsエリアの開始-->
			<div id="slidecont_header"><!--slidecont_headerエリアの開始-->
				<ul>
						<li class="slidecont_head_icon"><i class="far fa-user-circle fa-lg community"></i></li>
						<li class="slidecont_head_icon"><i class="fas fa-camera fa-lg camera"></i></li>
					<div id="slidecont_head_subtitle">
						<li><a href="index.php">コミュニティ</a></li>
						<li><a href="Photo.php">写真</a></li>
					</div>
				</ul>
			</div><!--slidecont_headerエリアの終了-->
			<div id="slidecont_main">
		        <ul>
		        	<li class="slidecont_icon"><a href="index.php">トップ</a></li>
		        	<li class="slidecont_icon"><a href="mypage.php">マイページ</a></li>
		        	<li class="slidecont_icon"><a href="login/logout.php">ログアウト</a></li>
		        </ul>
	    	</div>
	    </div><!--slidecontentsエリアの終了-->
	 </div>
	</div><!--slidebarエリアの終了-->
    

<script type="text/javascript" src="../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebars.min.js"></script><!--slidebarのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebar.js"></script><!--slidebarの相対リンク-->
<script type="text/javascript" src="../js/collapser/jquery.collapser.min.js"></script><!--collapserのjQueryリンク-->
<script type="text/javascript" src="../js/collapser/collapser.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/exif/jquery.exif.js"></script><!--jquery.exif.jsのリンク-->
<script type="text/javascript" src="../js/exif/binaryajax.js"></script><!---binaryajax.jsのリンク-->
<script type="text/javascript" src="../js/exif/exif.js"></script><!--exif.jsの相対リンク-->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmeS2HahteH0UuTK0gEgOXRLZi6jdGDx8&callback=initMap" async defer></script><!--googlemapのリンク-->
<script type="text/javascript" src="../js/googlemap/googlemap.js"></script><!--exif.jsの相対リンク-->
<script type="text/javascript" src="../js/exif/exif.js"></script><!--exif.jsの相対リンク-->

<script>	

//PHPから撮影した場所を取得
var photoPrice = document.getElementById("photo_location").innerText;


//googleMaps初期設定
function initMap() {
    console.log("checkPlace")
  
  //PHPセッションから取得した地区情報文字列をcheckPlaceに格納
  checkPlace = photoPrice;
  //緯度・軽度の初期化
  LatLng = new google.maps.LatLng("34.699875","135.493032");
  opts = {
    zoom: 18,
    center: LatLng,
    mapTypeId: google.maps.MapTypeId.ROADMAP,

  }
  //マップ生成をオブジェクト化
  mapObj = new google.maps.Map(document.getElementById("gmap"), opts);

  //１）ジオコードオブジェクトを生成（ジオコード：「住所」や「地域名」を地理的な座標に変換する処理）
  geocoder = new google.maps.Geocoder();

  //②geocode(メソッド)※Geocoderに付随するメソッド：住所や地域名情報を緯度経度に変換
  //メソッド書式：geocode(変換処理,コールバック)
  geocoder.geocode({'address':checkPlace,'region':'jp'},
      //↓↓geocodeメソッドのコールバック（下記、２種のパラメータはレスポンスされる）
      //result：結果値（GeocoderResult）オブジェクト（レスポンスされた、変換後の経緯度値）
      //status：問題無く処理出来たか（GeocoderStatus）クラス
      function(result,status){
      if(status == google.maps.GeocoderStatus.OK){
          mapObj.setCenter(result[0].geometry.location);
          gMarkerCenter.setPosition(result[0].geometry.location);
          //console.log(status)
          console.log(checkPlace)
      };//if
  });//geocode

  //マーカー生成をオブジェクト化
  gMarkerCenter = drawMarkerCenterInit(LatLng);
}//function initMap   
   

//マーカー生成関数
function drawMarkerCenterInit() {
    console.log("checkPlace")
  var markerCenter = new google.maps.Marker({
    map: mapObj,
    animation:google.maps.Animation.DROP
  });
  return markerCenter;
}





</script>
    
</body>
</html>


