( function ( $ ) {
    var controller = new slidebars();
    controller.init();
   
    $( '.menubtn' ).on( 'click', function ( event ) {
    event.preventDefault();
    event.stopPropagation();
   
    controller.toggle( 'sb-right' );
    } );
   
   } ) ( jQuery );
	