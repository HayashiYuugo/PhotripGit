<?php

session_start();//セッションの開始
require('db/dbconnect.php');//DBに接続

//ログイン処理
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


//セッションで撮影場所の値を持ち越し
$location_date = $_SESSION['location'];//撮影場所


//チャット機能エリア
if($_POST['message']) {//messageボタンを押した時に
    $message = $_POST;
    $insertMsg = $db->prepare("INSERT INTO community_chat (member_id,community_id,message,created) VALUE (?,?,?,NOW())");
    $insertMsg->execute(array(
        $member['id'],
        $_GET['id'],
        $message['message']
    ));
    header('Location: groupchat.php?id='.$_GET['id']);
    exit();
};

//チャット内容を取得する
$sql = 'SELECT community_chat.*,members.name,members.picture FROM community_chat INNER JOIN members ON community_chat.member_id = members.id WHERE community_id = \''.$_REQUEST['id'].'\' ORDER BY created DESC';
$stmt = $db->query($sql);



?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Photrip/グループチャット</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"><!--bootstrapのリンク-->
    <link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--h1のgoogleフォントリンク-->
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"><!--FontAwesomeのリンク-->
    <link rel="stylesheet" type="text/css" href="../css/Groupchat.css"><!--headerのcssリンク-->
</head>
<body>
    <div id="maincontents"><!--maincontentsエリアの開始-->

      <!--gmapエリア-->
      <div id="gmap"></div>

      <div id=chat_profile><!--chat_profileエリアの開始-->

        <div id="change_button"><!--change_buttonエリアの開始-->
          <button class="switch_area_switch_button2" id="width_change2"><i class="far fa-comment"></i></i></button>
          <button class="switch_area_switch_button" id="width_change1"><i class="fas fa-map-pin"></i></button><!--gmapが全画面-->
        </div><!--change_buttonエリアの終了-->

        <p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="profile_image"></p>

        <h1><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?></h1>

        <form method="post" action="">
          <input type="text" id="message" name="message" rows="3" placeholder="Aa" />
          <input type="submit" class="btn btn-success"></button>
        </form>

      </div><!--chat_profileエリアの終了-->

    
      <div id="chatting" onload="tm()"><!--chattingエリアの開始-->
      <?php
          foreach($stmt as $row) {//message出力PHPのforeach
              $str = $row['created'];
              $dt = date('A H時i分', strtotime($str));
      ?>
      <?php if($row['member_id'] === $member['id']){  ?><!--自分のmessageならば-->
        <div class="right_text"><!--右寄せの処理--><!--right_textエリアの開始-->
          <dl>
              <dt><img src="registrationimage/member_picture/<?php print(htmlspecialchars($row['picture'], ENT_QUOTES));?>" width="40" height="40"></dt>
              <dd id="<?php print(htmlspecialchars($row['name'], ENT_QUOTES));?>"><?php print(htmlspecialchars($row['name'], ENT_QUOTES)); ?></dd>
              <dd><?php print(htmlspecialchars($row['message'], ENT_QUOTES)); ?><span><?php print(htmlspecialchars($dt, ENT_QUOTES));?><span></dd>
          </dl>
        </div><!--right_textエリアの終了--->
    <?php } 
    else{ /*自分のコメントでなければ*/ 
    ?>
        <div class="left_text"><!--左寄せの処理--><!--left_textエリアの開始-->
          <dl>
              <dt><img src="registrationimage/member_picture/<?php print(htmlspecialchars($row['picture'], ENT_QUOTES));?>" width="40" height="40"></dt>
              <dd id="<?php print(htmlspecialchars($row['name'], ENT_QUOTES));?>"><?php print(htmlspecialchars($row['name'], ENT_QUOTES)); ?></dd>
              <dd><?php print(htmlspecialchars($row['message'], ENT_QUOTES)); ?><span><?php print(htmlspecialchars($dt, ENT_QUOTES));?><span></dd>
          </dl>
        </div><!--left_textエリアの終了-->
  <?php }?>
<?php } ?><!--message出力PHPのforeachの終了-->
      </div><!--chattingエリアの終了-->

      <p id="location_date"><?php print(htmlspecialchars($location_date, ENT_QUOTES));?></p>

      </div><!--maincontentsエリアの終了-->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmeS2HahteH0UuTK0gEgOXRLZi6jdGDx8&callback=initMap" async defer></script>
<script type="text/javascript" src="../js/googlemap/googlemap.js"></script><!--exif.jsの相対リンク-->
<script type="text/javascript" src="../js/googlemap/gmapwidthmax.js"></script><!--exif.jsの相対リンク-->
<script type="text/javascript" src="../js/googlemap/comgeocoder.js"></script><!--exif.jsの相対リンク-->

</body>
</html>


