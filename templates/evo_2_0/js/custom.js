/*
 * Platz für eigenes Javascript
 * Die hier gemachten Änderungen überschreiben ggfs. andere Funktionen, da diese Datei als letzte geladen wird.
 */

//used for tabs
//still learning. be kind.

//get tabs module parent
var tabsModule = document.body.querySelector(".megamenu");
var tabsModule2 = document.body.querySelector("#shop-nav");

//get tab nav
var tabNavList = document.body.querySelector(".megamenu .nav");
var tabNavList2 = document.body.querySelector("#shop-nav .header-shop-nav");
//get all tab nav links
var tabNavLinks = document.querySelectorAll(".megamenu .nav>li");
var tabNavLinks2 = document.querySelectorAll("#shop-nav .header-shop-nav>li:not(#search)");
//get tab nav current link indicator
var tabNavCurrentLinkindicator = tabNavList.querySelector(".TabNav_Indicator");
var tabNavCurrentLinkindicator2 = tabNavList2.querySelector(".TabNav_Indicator2");


/**
 * position indicator function
 */
function positionIndicator() {
    //get left position of tab nav ul
    var tabNavListLeftPosition = tabNavList.getBoundingClientRect().left;
    var tabNavListLeftPosition2 = tabNavList2.getBoundingClientRect().left;
    //get tab module parent current data value
    var tabsModuleSectionDataValue = tabsModule.getAttribute("data-active-tab") || "1";
    var tabsModuleSectionDataValue2 = tabsModule2.getAttribute("data-active-tab") || "1";
    //get nav link span with data value that matches current tab module parent data value
    var tabNavCurrentLinkText = tabNavList.querySelector("[data-tab='" + tabsModuleSectionDataValue + "'] a");
    var tabNavCurrentLinkText2 = tabNavList2.querySelector("[data-tab='" + tabsModuleSectionDataValue2 + "']");
    //get dimensions of current nav link span
    var tabNavCurrentLinkTextPosition = tabNavCurrentLinkText.getBoundingClientRect();
    var tabNavCurrentLinkTextPosition2 = tabNavCurrentLinkText2.getBoundingClientRect();
    //set indicator left position via CSS transform
    //current nav link span left position - tab nav ul left position
    //prefix me for live
    tabNavCurrentLinkindicator.style.transform =
        "translate3d(" +
        (tabNavCurrentLinkTextPosition.left - tabNavListLeftPosition +15) +
        "px,0,0) scaleX(" +
        (tabNavCurrentLinkTextPosition.width-30) * 0.01 +
        ")";

    tabNavCurrentLinkindicator2.style.transform =
        "translate3d(" +
        (tabNavCurrentLinkTextPosition2.left - tabNavListLeftPosition2 +15) +
        "px,0,0) scaleX(" +
        (tabNavCurrentLinkTextPosition2.width - 30) * 0.01 +
        ")";
}
/**
 * fire position indicator function right away
 */
positionIndicator();

/**
 * hide all tab panels function
 */
/*function hideAllTabPanels() {
    //loop through all tab panel elements
    for (i = 0; i < tabPanels.length; i++) {
        //remove style attribute from each tab panel element to hide them
        tabPanels[i].removeAttribute("style");
    }
};*/

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
    //fire hide all tab panels function
    //hideAllTabPanels();
    //get tab panel element with ID that matches this link href value and set its style to show it
    //thisTabPanel.style.display = "block";
    //fire the position indicator function
    positionIndicator();
};

var tabNavLinkEvent2 = function() {
    //get this link data value
    var thisLink2 = this.getAttribute("data-tab");
    //get this link href value
    var thisHref2 = this.getAttribute("href");
    //get tab panel element with ID that matches this link href value
    var thisTabPanel = document.querySelector(thisHref2);
    //set tab module parent data to this link data value
    tabsModule2.setAttribute("data-active-tab", thisLink2);
    //fire hide all tab panels function
    //hideAllTabPanels();
    //get tab panel element with ID that matches this link href value and set its style to show it
    //thisTabPanel.style.display = "block";
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
for (var i = 0; i < tabNavLinks2.length; i++) {
    //for each nav link, add click event that fires tab nav link click event function
    tabNavLinks2[i].addEventListener("mouseover", tabNavLinkEvent2, false);
}
/**
 * should really position indicator from parent left edge rather than body,
 * to keep indicator in position on resize. meh
 * for now, here's a quick win because i'm tired
 * https://developer.mozilla.org/en-US/docs/Web/Events/resize
 */
(function() {
    window.addEventListener("resize", resizeThrottler, false);
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