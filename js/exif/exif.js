


var file = $('#img').attr('src');
test(file);
function test(file){
    // サーバー上の画像ファイルから、Ajaxを利用してバイトデータを取得する
    var xhr = new XMLHttpRequest();
    xhr.open('GET', file, true);
    xhr.responseType = 'arraybuffer';
    xhr.onload = function(e) {

        // 画像ファイルのバイトデータを取得する
        var arrayBuffer = this.response;

        // バイトデータとコンテンツタイプからBlobを生成する
        var blob = new Blob([arrayBuffer], {type: "image/jpeg"});
        // BlobからExif情報を取得する
        $.fileExif(blob,function(exif){
    console.log(exif);

    var res = printProperties(exif);
    $('#EXIF_result').html(res);

        });

    };
    xhr.send();
}

function printProperties(obj) {

var properties = '';
for (var prop in obj){
    switch (prop) {
      case 'Make':/*メーカー */
        properties += 'メーカー' + ": " + obj[prop] + "<br>";
        break;
      case 'Model':/* 機種名*/
        properties += '機種名' + ": " + obj[prop] + "<br>";
        break;
      case 'FNumber':/*F値*/
        properties += '絞り値' + ": F" + obj[prop] + "<br>";
        break;
      case 'DateTimeOriginal':/*撮影日時 */
        properties += '撮影日時' + ": " + obj[prop] + "<br>";
        break;
      case 'DNGLensInfo':/*レンズ */
        properties += 'レンズ' + ": " + obj[prop] + "<br>";
        break;
      case 'CreatorTool':/*ソフト */
        properties += '使用ソフトウェア名' + ": " + obj[prop] + "<br>";
        break;
      case 'OLSpecialMode':/*撮影モード */
        properties += '撮影モード' + ": " + obj[prop] + "<br>";
        break;
      case 'WhiteBalance':/*ホワイトバランス */
        properties += 'ホワイトバランス' + ": " + obj[prop] + "<br>";
        break;
      case 'ShutterSpeedValue':/*シャッタースピード */
        properties += 'シャッタースピード' + ": " + obj[prop] + "<br>";
        break;
      case 'EXISOSensitivity':/*ISO感度 */
        properties += 'ISO感度' + ": " + obj[prop] + "<br>";
        break;
      case 'FocalLength':/**焦点距離 */
        properties += '焦点距離' + ": " + obj[prop] + "<br>";
        break;
      case 'Flash':/**フラッシュ */
        properties += 'フラッシュ' + ": " + obj[prop] + "<br>";
        break;
      case 'ColorSpace	':/**色空間情報 */
        properties += '色空間情報' + ": " + obj[prop] + "<br>";
        break;
      case 'EXImageSize':/*写真サイズ */
        properties += '写真サイズ' + ": " + obj[prop] + "<br>";
        break;
      case 'ExposureTime':/**露出時間 */
        properties += '露出時間' + ": " + obj[prop] + "<br>";
        break;
      case 'Contrast':/**コントラスト*/
        properties += 'コントラスト' + ": " + obj[prop] + "<br>";
        break;
    }
}

return properties;
}


