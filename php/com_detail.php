<?php 

session_start();//セッションの開始

require('db/dbconnect.php');//DB接続ファイルを読み込み。

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


//コミュニティ投稿の詳細を1件表示するための処理 
//コミュンニティ作成内容をDBからSELECTで取得
$coms = $db->prepare('SELECT m.name,m.picture, c.* FROM members
m, community c WHERE m.id=c.member_id AND c.id=?'); 

$coms->execute(array($_REQUEST['id']));
if($row = $coms->fetch(PDO::FETCH_ASSOC)){
    $comtitle = $row['comtitle'];
    $thumimg = $row['thumimg'];
    $numrest = $row['numrest'];
    $date = $row['date'];
    $location = $row['location'];
    $subject = $row['subject'];
    $usecamera = $row['use_camera'];
    $comment = $row['comment'];
    $created = $row['created'];
    $modified  = $row['modified'];
    $compicture = $row['picture'];
    $comname = $row['name'];
}

//カメラ情報投稿時に時に数字をカメラ名に変換
$camera = [
	0 => 'デジタル一眼レフカメラ',
	1 => 'スマートフォン',
	2 => 'ミラーレス一眼カメラ',
	3 => 'コンパクトデジタルカメラ',
	4 => 'ドローン',
	5 => 'ビデオカメラ'
];

//撮影場所をセッションにいれる処理
$_SESSION['location'] = $location;


/* コミュニティ参加の処理*/
if($_POST['enter']) {//投稿ボタンをクリックした時

    //現在ログイン者がコミュニティに参加しているか判定する処理
    $sql = 'SELECT COUNT(*) AS count from member_community where member_id = '.$member["id"].' and community_id = \''.$_REQUEST['id'].'\';';
    $stmt = $db->query($sql);
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $com_member_id = $row['count'];
        }
    if($com_member_id <= 0){//コミュニティに参加していなければ
        //情報をDBにINSERT(参加)
        $ins_com_member = $db->prepare('INSERT INTO member_community (member_id,community_id,created) VALUE (?,?,NOW())');
        $ins_com_member->execute(array(
            $member['id'],//memberテーブルのid
            $_GET['id']//communityテーブルのid(URLから取得)
        ));
        //groupchat.phpに移動
        header('Location: groupchat.php?id='.$_GET['id']);
        exit();
    }
    else{//参加していればINSERTせずに移動
        header('Location: groupchat.php?id='.$_GET['id']);
        exit();
    }
}



//コミュニティ人数が何人か取得する処理
$sql = 'SELECT COUNT(*) AS member_count FROM member_community WHERE community_id = \''.$_REQUEST['id'].'\';';
$stmt = $db->query($sql);
if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //コミュニティ人数が$com_member_countに代入
    $com_member_count = $row['member_count']; 
}

//  コミュニティ参加の処理
$entry_com = $db->query('SELECT count(member_id) AS count FROM member_community WHERE community_id = \''.$_REQUEST['id'].'\';');




/*コミュニティのコメント投稿機能 */
if($_POST['com_comment']) {/*コメントエリアが空でなければ */
  $reply = $_POST;
  $insert_com_reply = $db->prepare("INSERT INTO com_reply_comment (member_id,community_id,comment,created) VALUE (?,?,?,NOW())");
  $insert_com_reply->execute(array(
  $member['id'],
  $_GET['id'],
  $reply['com_comment']
));
header('Location: com_detail.php?id='.$_GET['id']);
exit();
}

//コミュニティのコメントを表示する処理
$comments = $db->query('SELECT community.*,com_reply_comment.*,(SELECT name FROM members WHERE com_reply_comment.member_id = id) as member_name, (SELECT picture FROM members WHERE com_reply_comment.member_id = id) as member_picture FROM community,com_reply_comment WHERE community.id=com_reply_comment.community_id and community.id=\''.$_REQUEST['id'].'\' ORDER BY com_reply_comment.id DESC;'); 



//このコミュニティに参加してるmemberを取得
$sql = 'SELECT members.name,members.picture FROM member_community INNER JOIN members ON member_community.member_id = members.id AND member_community.community_id = \''.$_REQUEST['id'].'\'';
$stmt = $db->query($sql);



//コミュニティ宛に投稿された画像を表示
$memberPhoto = [];

$memberPhotosql = 'SELECT members.name,members.picture,photoposts.* FROM photoposts INNER JOIN members ON photoposts.member_id = members.id WHERE community_id =  \''.$_REQUEST['id'].'\'';
$st = $db->query($memberPhotosql);
$otherPhoto = $st->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Re:Photo/写真詳細</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous"><!--bootstrapのリンク-->
    <link href="https://fonts.googleapis.com/css?family=Dancing+Script|Indie+Flower" rel="stylesheet"><!--h1のgoogleフォントリンク-->
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"><!--FontAwesomeのリンク-->
    <link rel="stylesheet" type="text/css" href="../css/slidebar/slidebars.min.css"><!--index.htmlのcssリンク-->
	<link rel="stylesheet" type="text/css" href="../css/slidebar/Slidebar.css"><!--slidebar.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/collapser/collapser.css"><!--collapser.cssのリンク-->
	<link rel="stylesheet" type="text/css" href="../css/com_header.css"><!--headerのcssリンク-->
    <link rel="stylesheet" type="text/css" href="../css/com_detail.css"><!--photo_detail.cssの相対リンク-->
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+SC:100" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:300" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans+TC:100" rel="stylesheet">
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
                            <div class="Photo">
                                <dt><a href="Photo.php"><i class="fas fa-camera-retro"></i></a></dt>
                                <dd>写真</dd>
                            </div>
                            <div class="Notification">
                                <dt><i class="far fa-bell"></i></dt>
                                <dd>通知</dd>
                            </div>
                            <div class="Upload">
                                <dt><a href="index.php"><i class="fas fa-cloud-upload-alt" id="uploadbtn"></i></a></dt>
                                <dd>投稿</dd>		
                            </div>
                            <div class="Menu">
                                <dt><i class="fas fa-bars menubtn"></i></dt>
                                <dd>メニュー</dd>
                            </div>
                    </div><!--header_navエリアの終了-->
            </div><!--heaerエリアの終了-->

            <p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="profile_image"></p>

            <div id="com_detail_header"><!--com_detail_headerエリアの開始-->
                <div id="com_header_left"><!--com_header_leftエリアの開始-->
                    <p><img src="registrationimage/thumimg/<?php print(htmlspecialchars($thumimg, ENT_QUOTES)); ?>" width="250" height="200"></p>
                </div><!--com_header_leftエリアの終了-->

                <div id="com_header_right">
                    <div id="com_header_title"><!---com_header_titleエリアの開始-->
                            <h2><?php print(htmlspecialchars($comtitle,ENT_QUOTES));?></h2>
                        </div><!--com_header_titleエリアの終了-->
                        <div id="member_entry"><!--member_emtryエリアの開始-->
                            <div id="com_header_membercount"><!--com_header_membercountエリアの開始-->
                                <h3>メンバー数<span class="numrestcount"><?php print(htmlspecialchars( $com_member_count));?></span>人</h3>
                            </div><!--com_header_membercountエリアの終了-->
                            <div id="numrest">
                                <p><span class="numrestcount"><?php print(htmlspecialchars($numrest, ENT_QUOTES)); ?></span>人まで</p>
                            </div>
                        </div><!--member_entryエリアの終了-->
                        <div id="com_header_entry"><!--com_header_entryエリアの開始-->
                                
<script>
    /**
     *コミュニティの返り値によりフォーム送信
    */
    function submitChk () {
        /* 確認ダイアログ表示 */
        var enterconf = confirm ( "参加してもよろしいですか？\n\n参加しない場合は[キャンセル]ボタンを押して下さい");
        /* send_flg が TRUEなら送信、FALSEなら送信しない */
        return enterconf;
    }
</script> 

                            <form action="" method="POST" id="sweetalert" onsubmit="return submitChk()" target="_blank"><!--コミュニティ参加エリアの開始-->
                                <input type="submit" class="btn btn-success" value="参加する" name="enter" id="enter"><a href="groupchat.php?id=<?php print(htmlspecialchars($_GET['id'], ENT_QUOTES));?>" target="_blank"></a></button>
                            </form>
                        </div><!--com_header_entryエリアの終了-->
                </div><!--com_headder_titleエリアの終了-->

                <div id="com_detail_comment">
                    <p><?php print(htmlspecialchars($comment, ENT_QUOTES)); ?></p>
                </div>

            </div><!--com_detail_headerエリアの終了-->

            <div id="com_detail_main"><!--com_detail_mainエリアの開始-->
                <div id="com_main_left"><!--com_main_leftエリアの開始-->
                    <dl>
                        <dt id="com_location">撮影場所</dt>
                            <dd><?php print(htmlspecialchars($location, ENT_QUOTES)); ?></dd>
                        <dt id="com_date">撮影日時</dt>
                            <dd><?php $str = $date;
                                        $dt = date('Y年m月d日', strtotime($str));
                                        print(htmlspecialchars($dt, ENT_QUOTES)); ?></dd>
                        <dt id="subject">被写体</dt>
                            <dd><?php print(htmlspecialchars($subject, ENT_QUOTES)); ?></dd>
                        <dt id="use_camera">主な使用カメラ</dt>
                            <dd><?php print(htmlspecialchars($camera[$usecamera], ENT_QUOTES));?></dd>
                        <dt id="creator">開設者</dt>
                            <dd><img src="registrationimage/member_picture/<?php print(htmlspecialchars($compicture, ENT_QUOTES)); ?>" width="40" height="40"></dd>
                            <dd><?php print(htmlspecialchars($comname, ENT_QUOTES)); ?></dd>
                        <dt id="opning_date">開設日</dt>
                        <dd><?php 
                                $str = $created;
                                $dt = date('Y年m月d日', strtotime($str));
                                print(htmlspecialchars($dt,ENT_QUOTES));?></dd>
                    </dl>
                </div><!--com_main_leftエリアの終了-->
                <div id="com_main_middle"><!--com_main_middleエリアの開始-->
                    <h3 id="participant">参加メンバー</h3>
                            <dl> 
                                <?php
                                //参加メンバーを表示
                                    foreach($stmt as $row) {
                                        print('<div id="members">');
                                        print('<dt><img src="registrationimage/member_picture/' . htmlspecialchars($row["picture"], ENT_QUOTES) . '" width="35" height="35" /></dt>');
                                        print('<dd>'.htmlspecialchars($row["name"], ENT_QUOTES) .'</dd>');
                                        print('</div>');
                                    }
                                ?>
                            </dl>
                </div><!--com_main_middleエリアの終了-->
                <div id="com_main_right"><!--com_main_rightエリアの開始-->
                     
                    <form action="#" method="POST">
                        <div id="comment_header"><!--com_right_headerエリアの開始-->
                            <h3 id="comment_icon">コメント</h3>  
                            <textarea class="form-control" rows="4" placeholder="コメントをお書きください。" name="com_comment" id="com_comment"></textarea>
                            <button type="submit" class="btn btn-primary">投稿する</button>  
                        </div><!--comment_headerエリアの終了-->

                        <div id="commentcontents"><!--commentcontentsエリアの開始-->
    <?php foreach($comments as $comment){?>
                            <div id="comment_result"><!--comment_resultエリアの開始-->
                                <div id="comment_left"><!--comment_leftエリアの開始-->
                                    <p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($comment['member_picture'], ENT_QUOTES));?>" class="member_img" width="40" height="40"></p>
                                </div><!--comment_leftエリアの終了-->
                                <div id="comment_right"><!--comment_rightエリアの開始-->
                                    <h4><?php print(htmlspecialchars($comment['member_name'], ENT_QUOTES)); ?></h4>
                                    <p><?php print(htmlspecialchars($comment['comment'], ENT_QUOTES)); ?></p>
                                </div><!--comment_rightエリアの終了-->
                                <p id="comment_date"><?php 
                                                $str = $comment['created'];
                                                $dt = date('m月d日 H時i分', strtotime($str)); 
                                                print(htmlspecialchars($dt, ENT_QUOTES)); ?></p>
                            </div><!--comment_resultエリアの終了-->
    <?php }?>
                        </div><!--commentcontentsエリアの終了-->
                    </form>                            
                </div><!--com_main_rightエリアの終了-->
            </div><!--com_detail_mainエリアの終了-->

            <div id="othercontents"><!--othercontentsエリアの開始-->
                    <h4><?php print(htmlspecialchars($comtitle,ENT_QUOTES)); ?>に投稿された写真</h4>
                <div id="entryComPhoto"><!--entryComPhotoエリアの開始-->
<?php foreach ($otherPhoto as $otherphoto):?>
					<div class="photodisplay"><!--photodisplayエリアの開始-->
						<h2><a href="photo_detail.php?id=<?php print(htmlspecialchars($otherphoto['id'], ENT_QUOTES)); ?>"><img src="registrationimage/photoimg/<?php print(htmlspecialchars($otherphoto['photoimg'], ENT_QUOTES)); ?>" width="380" height="285"></h2>
						<div class="mask"><!--maskエリアの開始-->
							<div id="mask_header" class="text-white"><!--mask_headerエリアの開始-->
								<p><?php print(htmlspecialchars($otherphoto['photolocation'], ENT_QUOTES)); ?></p>
							</div><!--mask_headerエリアの終了-->
							<div id="mask_profile" class="text-white"><!--mask_profileエリア開始了-->
								<ul>
									<li><img src="registrationimage/member_picture/<?php print(htmlspecialchars($otherphoto['picture'], ENT_QUOTES)); ?>" width="30" height="30"><li>
									<li><?php print(htmlspecialchars($otherphoto['name'], ENT_QUOTES)); ?></li> 
								</ul>
							</div><!--mask_profielエリアの終了-->
							<div id="mask_footer"><!--mask_footerエリアの開始-->
								<ul>
									<li><i class="icon_color far fa-heart fa-lg"></i></li>
									<p id="count_comment"><?= $idcom[$otherphoto['id']]; ?></p>
									<li><i class="icon_color far fa-comment-alt fa-lg"></i></li>
								</ul>
							</div><!--mask_fotterエリアの終了-->
						</div><!--maskエリアの終了-->
					</div><!--photodisplay-エリアの終了-->
<?php endforeach; ?>	
                </div><!--entryComPhotoエリアの終了-->
            </div><!--entryComPhotoエリアの終了-->

        </div><!--wrapperエリアの終了-->
    </div><!--containerエリアの終了-->

    <div off-canvas="sb-right right push"><!--slidebar表示エリアの開始-->
		<div id="slidecontents"><!--slidecontentsエリアの開始-->
			<div id="slidecont_header"><!--slidecont_headerエリアの開始-->
				<ul>
						<li class="slidecont_head_icon"><i class="far fa-user-circle fa-lg"></i></li>
						<li class="slidecont_head_icon"><i class="fas fa-camera fa-lg camera"></i></li>
					<div id="slidecont_head_subtitle">
						<li><a href="index.php">コミュニティ</a></li>
						<li><a href="Photo.php">写真</a></li>
					</div><!--slidecont_head_subtitleの終了-->
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
    </div><!--slidebar表示エリアの終了-->

<script type="text/javascript" src="../js/jquery-2.0.2.min.js"></script><!--jQueryのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebars.min.js"></script><!--slidebarのリンク-->
<script type="text/javascript" src="../js/slidebar/slidebar.js"></script><!--slidebarの相対リンク-->
<script type="text/javascript" src="../js/collapser/jquery.collapser.min.js"></script><!--collapserのjQueryリンク-->
<script type="text/javascript" src="../js/sweetalert/sweetalert2.min.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/collapser/collapser.js"></script><!--collapserの相対リンク-->
<script type="text/javascript" src="../js/sweetalert/sweetert2.js"></script><!--collapserの相対リンク-->

<script>

var entermem = document.getElementById("detail_participant");

</script>
    
</body>
</html>


                     