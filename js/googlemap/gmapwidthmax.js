//画面切り替え（gmapエリア拡大）
gmapWidth = 0;

//gmapが画面いっぱいに表示される処理
$("#width_change1").click(function(){//width_changeをクリックした時に
    $("#gmap").animate({'width':'100%'},callback);
    $("#chat_profile").animate({ 'width':'0%'});
    $("#chatting").animate({'width':'0%'});
    $("#change_button").fadeOut(100);
    //$("#change_button").animate({'overflow':'hidden','position':'absolute','top':'100px','left':'98%'},callbackBtnAnim);
});

//chatt画面をいっぱいにする処理
$("#width_change2").click(function(){//width_changeをクリックした時に
    $("#chatting").animate({'width':'30%'});
    $("#chat_profile").animate({ 'width':'20%'});
    $("#gmap").animate({'width':'50%'},callback2);
    $("#change_button").fadeOut(100);
    //$("#change_button").animate({'overflow':'hidden','position':'absolute','top':'100px','left':'48%'},callbackBtnAnim2);
});

function callback(){
    $("#width_change1").hide();
    $("#change_button")
        .css({'position':'absolute','top':'100px','left':'98%'})
        .fadeIn(100);
}
function callback2(){
    $("#change_button")
        .css({'position':'absolute','top':'100px','left':'48%'})
        .fadeIn(100);
    $("#width_change1").show();
}