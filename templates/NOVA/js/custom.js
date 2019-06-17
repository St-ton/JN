/*
 Platz für eigenes Javascript
 Die hier gemachten Änderungen überschreiben ggfs. andere Funktionen, da diese Datei als letzte geladen wird.
*/

function ready(fn) {
    if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading"){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
};

function setupNav() {
    var wee = document.querySelector('.wee').style,
        navList = document.querySelectorAll('.megamenu .nav-item'),
        currentNav = document.querySelector('.megamenu .nav-item.active');

    if (currentNav != undefined) {
        wee['left'] = currentNav.offsetLeft + 'px';
        wee['width'] = currentNav.offsetWidth + 'px';
    } else {
        wee['width'] = 0 + 'px';
    }

    // note: The addEventListener() method is not supported in Internet Explorer 8 and earlier versions//
    navList.forEach(function(nav){
        nav.addEventListener('click', function() {
            var leftPos = parseInt(Math.round($('#navbarToggler').scrollLeft()));
            wee['left'] = this.offsetLeft - leftPos + 'px';
            wee['width'] = this.offsetWidth + 'px';
        });
    });
};

ready(function(){
    setupNav();
});