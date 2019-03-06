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
<style>

</style>
<body>
    <div id="maincontents"><!--maincontentsエリアの開始-->

    <!--gmapエリア-->
    <div id="gmap"></div>


    <div id=chat_profile><!--chat_profileエリアの開始-->

    <div id="change_button"> 
    <button class="switch_area_switch_button2" id="width_change2"><i class="far fa-comment"></i></i></button>
    <button class="switch_area_switch_button" id="width_change1"><i class="fas fa-map-pin"></i></button><!--gmapが全画面-->
    </div>

    <p><img src="registrationimage/member_picture/<?php print(htmlspecialchars($member['picture'], ENT_QUOTES));?>" id="profile_image"></p>

    <h1><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?></h1>


    <form method="post" action="">
    <input type="text" id="message" name="message" rows="3" placeholder="Aa" />
    <!-- <input type="button" value="送信" onclick="write_message()"> -->
    <input type="submit" class="btn btn-success"></button>
    </form>

    </div><!--chat_profileエリアの終了-->


    
    <div id="chatting" onload="tm()"><!--chattingエリアの開始-->


    <?php
        foreach($stmt as $row) {//message出力PHPのforeach
            $str = $row['created'];
            $dt = date('A H時i分', strtotime($str));
    ?>
    <?php if($row['member_id'] === $member['id']){  ?><!--もし、自分のmessageならば-->
        <div class="right_text"><!--右寄せの処理--><!--right_textエリアの開始-->
        <dl>
            <dt><img src="registrationimage/member_picture/<?php print(htmlspecialchars($row['picture'], ENT_QUOTES));?>" width="40" height="40"></dt>
            <dd id="<?php print(htmlspecialchars($row['name'], ENT_QUOTES));?>"><?php print(htmlspecialchars($row['name'], ENT_QUOTES)); ?></dd>
            <dd><?php print(htmlspecialchars($row['message'], ENT_QUOTES)); ?><span><?php print(htmlspecialchars($dt, ENT_QUOTES));?><span></dd>
        </dl>
        </div><!--right_textエリアの終了--->
        <?php } 
         else{ ?><!--もし、自分のmessageでなかったら-->
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

  <!--<script type="text/javascript" src="../js/jquery-2.0.2.min.js"></script>--><!--javascriptのリンク-->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmeS2HahteH0UuTK0gEgOXRLZi6jdGDx8&callback=initMap" async defer></script>
<script type="text/javascript" src="../js/googlemap/googlemap.js"></script><!--exif.jsの相対リンク-->
<script type="text/javascript" src="../js/googlemap/gmapwidthmax.js"></script><!--exif.jsの相対リンク-->
<script>


//PHPセッションから取得した文字列を取得してpriceに格納
price = document.getElementById("location_date").innerText;

$(function(){
    console.log(price)
})

//googleMaps初期設定
function initMap() {
    console.log("checkPlace")
  
  //PHPセッションから取得した地区情報文字列をcheckPlaceに格納
  checkPlace = price;
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


// $(function(){
//     checkPlace = price;
//     console.log(checkPlace)//変数内の値確認様（最終は不要）

//     //１）ジオコードオブジェクトを生成（ジオコード：「住所」や「地域名」を地理的な座標に変換する処理）
//     geocoder = new google.maps.Geocoder();

//     //②geocode(メソッド)※Geocoderに付随するメソッド：住所や地域名情報を緯度経度に変換
//     //メソッド書式：geocode(変換処理,コールバック)
//     geocoder.geocode({'address':checkPlace,'region':'jp'},
//         //↓↓geocodeメソッドのコールバック（下記、２種のパラメータはレスポンスされる）
//         //result：結果値（GeocoderResult）オブジェクト（レスポンスされた、変換後の経緯度値）
//         //status：問題無く処理出来たか（GeocoderStatus）クラス
//         function(result,status){
//         if(status == google.maps.GeocoderStatus.OK){
//             mapObj.setCenter(result[0].geometry.location);
//             gMarkerCenter.setPosition(result[0].geometry.location);
//             //console.log(status)
//             console.log(checkPlace)
//         };//if
//     });//geocode
// })


//非同期処理
/*$(function(){
   
       
          url　: 'document.json',
          dataType : 'json',
          success　: function(data){
              console.log("success")
            console.log(price);
            for(i=0;i<data.recomendItem.length;i++){
              $("#data_list").append("<div id=\"data_id" + i + "\"></div>") ;
              $("#data_id"+i).css("backgroundColor","pink");
              $("#data_list #data_id"+i).append("<h2>" + data.recomendItem[i].itemName + "</h2>") ;
              $("#data_list #data_id"+i).append("<p>" + data.recomendItem[i].itemPrice + "</p>") ;
              $("#data_list #data_id"+i).append("<p>" + data.recomendItem[i].itemManu + "</p>") ;
              $("#data_list #data_id"+i).append("<p><img src='" + data.recomendItem[i].itemImg + "'></p>") ;

              $("#data_list #data_id"+i).click(function(){
                $("#itemDitail").empty();
                alert($(this).find("h2").text());
                var testItem = $(this).attr("id").slice(-1);

                console.log(testItem)
                console.log(price);
                $.ajax({
                    url:'',
                    dataType:'',
                    success:function(dataItem){
                    $("#itemDitail").show().append("<div id=\"itemDitailIn\"></div>") ;
                        $("#data_id"+i).css("backgroundColor","white");
                        $("#itemDitailIn").append("<h2>" + data.recomendItem[testItem].itemName + "</h2>") ;
                        $("#itemDitailIn").append("<p>" + data.recomendItem[testItem].itemPrice + "</p>") ;
                        $("#itemDitailIn").append("<p>" + data.recomendItem[testItem].itemManu + "</p>") ;
                        $("#itemDitailIn").append("<p><img src='" + data.recomendItem[testItem].itemImg + "'></p>") ;


                    //↓↓↓↓↓下記の処理がmapの表示位置変更とマーカー連携の処理（ここが重要）
                    //江口へ：このサンプル自体が作成中のプログラムに沿ってるので、変数などを変えればそのまま使えると思います。
                    //林へ：このサンプルではjsonから取得した「地域名（京セラドームなど）」を位置情報に変換しています。
                    //　　　よって、下記の（checkPlace）に格納する値を写真から取得した緯度経度にすれば行けると思う。
                    //　　　例）checkPlace = new google.maps.LatLng(Lat,Lng);この引数のLatとLngに各値を格納し
                    //　　　　　とすれば行けるのではと思います。


                   //=====googleMapsのgeocoder()メソッドを活用=====
                   //非同期で、１）mapの表示位置、２）マーカーセットする。

                   //jsonから取得した「地域名（「京セラドーム」など）」を変数にセット（checkPlace）
                   checkPlace = price;
                   console.log(checkPlace)//変数内の値確認様（最終は不要）

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
                      };//if
                    });//geocode
                    },//success
                    error:function(){
                        alert("click NG!")
                    }
                })//ajax
              })//for
            };//success
          },
          error: function(data){
              console.log("エラー")
          }
     });//ajax
   //itemDitailIn click（非同期画面の消去）
   $("#itemDitail").click(function(){
    console.log("itemDitailIn click")
    $(this).fadeOut(300).empty();
   });
});//function
*/








</script>
</body>
</html>


