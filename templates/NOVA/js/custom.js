/*
 * Platz für eigenes Javascript
 * Die hier gemachten Änderungen überschreiben ggfs. andere Funktionen, da diese Datei als letzte geladen wird.
 */

//get tabs module parent
var tabsModule = document.body.querySelector("#navbarToggler");
//get tab nav
var tabNavList = document.body.querySelector(".megamenu");
//get all tab nav links
var tabNavLinks = document.querySelectorAll(".megamenu>.nav-item");
//get tab nav current link indicator
var tabNavCurrentLinkindicator = tabsModule.querySelector(".TabNav_Indicator");

/**
 * position indicator function
 */
function positionIndicator() {
    //get left position of tab nav ul
    var tabNavListLeftPosition = tabNavList.getBoundingClientRect().left;
    //get tab module parent current data value
    var tabsModuleSectionDataValue = tabsModule.getAttribute("data-active-tab") || "1";
    //get nav link span with data value that matches current tab module parent data value
    var tabNavCurrentLinkText = tabNavList.querySelector("[data-tab='" + tabsModuleSectionDataValue + "']");
    //get dimensions of current nav link span
    var tabNavCurrentLinkTextPosition = tabNavCurrentLinkText.getBoundingClientRect();
    //set indicator left position via CSS transform
    //current nav link span left position - tab nav ul left position
    tabNavCurrentLinkindicator.style.transform =
        "translate3d(" +
        (tabNavCurrentLinkTextPosition.left - tabNavListLeftPosition + 15 ) +
        "px,0,0) scaleX(" +
        (tabNavCurrentLinkTextPosition.width) * 0.01 +
        ")";
}

/**
 * hide position indicator
 */
function hidePositionIndicator() {
    //get left position of tab nav ul
    var tabNavListLeftPosition = tabNavList.getBoundingClientRect().left;
    //get tab module parent current data value
    var tabsModuleSectionDataValue = tabsModule.getAttribute("data-active-tab") || "1";
    //get nav link span with data value that matches current tab module parent data value
    var tabNavCurrentLinkText = tabNavList.querySelector("[data-tab='" + tabsModuleSectionDataValue + "']");
    //get dimensions of current nav link span
    var tabNavCurrentLinkTextPosition = tabNavCurrentLinkText.getBoundingClientRect();
    tabNavCurrentLinkindicator.style.transform =
        "translate3d(" +
        (tabNavCurrentLinkTextPosition.left - tabNavListLeftPosition + 15 ) +
        "px,0,0) scaleX(0)";
}

/**
 * tab nav link function
 * tab nav link event shows matching panel and positions the indicator
 */
var tabNavLinkEvent = function() {
    //get this link data value
    var thisLink = this.getAttribute("data-tab");
    //get this link href value
    var thisHref = this.getAttribute("href");
    //get tab panel element with ID that matches this link href value
    var thisTabPanel = document.querySelector(thisHref);
    //set tab module parent data to this link data value
    tabsModule.setAttribute("data-active-tab", thisLink);
    //fire the position indicator function
    positionIndicator();
};

/**
 * loop through all nav links and add event
 * need to change to parent element and use e.target maybe
 */
for (var i = 0; i < tabNavLinks.length; i++) {
    //for each nav link, add click event that fires tab nav link click event function
    tabNavLinks[i].addEventListener("mouseover", tabNavLinkEvent, false);
}

/**
 * should really position indicator from parent left edge rather than body,
 * to keep indicator in position on resize.
 */
(function() {
    window.addEventListener("resize", resizeThrottler, false);
    tabsModule.addEventListener("mouseleave", hidePositionIndicator, false);
    //someone smarter than me code
    var resizeTimeout;
    function resizeThrottler() {
        if (!resizeTimeout) {
            resizeTimeout = setTimeout(function() {
                resizeTimeout = null;
                actualResizeHandler();
            }, 66);
        }
    }
    //function to fire after resize timeout delay
    function actualResizeHandler() {
        //fire the position indicator function
        positionIndicator();
    }

    jQuery('img.svg').each(function(){
        var $img = jQuery(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        jQuery.get(imgURL, function(data) {
            // Get the SVG tag, ignore the rest
            var $svg = jQuery(data).find('svg');

            // Add replaced image's ID to the new SVG
            if(typeof imgID !== 'undefined') {
                $svg = $svg.attr('id', imgID);
            }
            // Add replaced image's classes to the new SVG
            if(typeof imgClass !== 'undefined') {
                $svg = $svg.attr('class', imgClass+' replaced-svg');
            }

            // Remove any invalid XML tags as per http://validator.w3.org
            $svg = $svg.removeAttr('xmlns:a');

            // Replace image with new SVG
            $img.replaceWith($svg);

        }, 'xml');

    });
})();
