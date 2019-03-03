	//背景画像アニメーション
$('#wrapper').bgSwitcher({
    images: ['../../images/camera-2598507_1920.jpg','../../images/bailey-littlejohn-573133-unsplash.jpg','../../images/evening-1777352_1920.jpg','../../images/lens-1209823_1920.jpg','../../images/evan-kirby-142074-unsplash.jpgg','../../images/women-2608147_1920.jpg'], // 切り替える背景画像を指定
	interval: 5000,//3秒ごとに切り替える
	shuffle: true,//画像の切り替わりをシャッフル
	effect: "fade",
	duration: 2000,
});