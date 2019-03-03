$(function(){

    var myTime = new Date();
    
    var myHours = myTime.getHours();
    
    if(myHours <= 5) {//5時以下なら
        document.getElementById("header_after_image").innerHTML = '<p>こんばんは<br>今から撮影ですか？お気をつけて！</p>';
        var bgImg = document.getElementById("header_after_image").style;
        bgImg.backgroundImage = "url(../images/18303277304_da858cb448_o.jpg)";
        bgImg.backgroundPosition = "center center";
    }
        else　if(myHours <= 11){//11時以下なら
        document.getElementById("header_after_image").innerHTML = '<p>おはようございます<br>朝の撮影にいってらっしゃい！</p>';
        var bgImg = document.getElementById("header_after_image").style;
        bgImg.backgroundImage = "url(../images/photographer-1149781_1920.jpg)";
        bgImg.backgroundPosition = "center center";
    }
    
    else if(myHours <= 17){//17時以下なら
        document.getElementById("header_after_image").innerHTML = '<p>こんにちは<br>あなたに良い撮影ライフを！</p>';
        var bgImg = document.getElementById("header_after_image").style;
        bgImg.backgroundImage = "url(../images/camera-3466039_1920.jpg)";
        bgImg.backgroundPosition = "center 70%";
    }
    else if(myHours <= 23){//23時以下なら
        document.getElementById("header_after_image").innerHTML = '<p>こんばんは<br>夜の撮影にいってらっしゃい！</p>';
        var bgImg = document.getElementById("header_after_image").style;
        bgImg.backgroundImage = "url(../images/photographer-1149781_1920.jpg)";
        bgImg.backgroundPosition = "center center";
    }
    });
        