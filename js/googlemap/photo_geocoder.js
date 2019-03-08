
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


