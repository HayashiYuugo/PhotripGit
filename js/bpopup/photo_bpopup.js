 //bpopupのjquery1
 var controller = new slidebars();
 controller.init();
 
 //コミュニティ作成のポップアップの処理
 $('#uploadbtn').click(function() {
    $('#image_post_screen').bPopup({
      follow: [false, false],
      speed: 500,
      position: [80, 100],
  transition: 'fadeIn',
  transitionClose:'fadeIn',
    });
  
 });
 