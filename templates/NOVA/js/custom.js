/*
 Platz für eigenes Javascript
 Die hier gemachten Änderungen überschreiben ggfs. andere Funktionen, da diese Datei als letzte geladen wird.
*/
let zoom = $('.imagezoom');


function setImage(src, callback) {
    zoom.on('load',callback);
    zoom.attr('src', src);
}

$('.maximize').on('click',function(){
    let img = $(this).parent().find('img')[0],
        src = img.getAttribute('data-lg-img-src');

    if(src){
        setImage(src, function () {
            zoom.toggleClass('full');
            zoom.off('load');
        })
    }
});

zoom.on('click',function(){
    zoom.removeClass('full');
});
// schließt schon das modal
/*$( document ).on( 'keydown', function ( e ) {
    if ( e.keyCode === 27 ) { // ESC
        zoom.removeClass('full');
    }
});*/

if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    $('.cfg-group-image').addClass('mobile');
}