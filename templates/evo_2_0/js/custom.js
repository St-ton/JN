/*
 * Platz für eigenes Javascript
 * Die hier gemachten Änderungen überschreiben ggfs. andere Funktionen, da diese Datei als letzte geladen wird.
 */

//used for tabs
//still learning. be kind.

//get tabs module parent
var tabsModule = document.body.querySelector(".megamenu");
//get tab nav
var tabNavList = document.body.querySelector(".megamenu .nav");
//get all tab nav links
var tabNavLinks = document.querySelectorAll(".megamenu .nav>li");
//get tab nav current link indicator
var tabNavCurrentLinkindicator = tabNavList.querySelector(".TabNav_Indicator");


/**
 * position indicator function
 */
function positionIndicator() {
    //get left position of tab nav ul
    var tabNavListLeftPosition = tabNavList.getBoundingClientRect().left;
    //get tab module parent current data value
    var tabsModuleSectionDataValue = tabsModule.getAttribute("data-active-tab") || "1";
    //get nav link span with data value that matches current tab module parent data value
    var tabNavCurrentLinkText = tabNavList.querySelector("[data-tab='" + tabsModuleSectionDataValue + "'] a");
    //get dimensions of current nav link span
    var tabNavCurrentLinkTextPosition = tabNavCurrentLinkText.getBoundingClientRect();
    //set indicator left position via CSS transform
    //current nav link span left position - tab nav ul left position
    //prefix me for live
    tabNavCurrentLinkindicator.style.transform =
        "translate3d(" +
        (tabNavCurrentLinkTextPosition.left - tabNavListLeftPosition +15) +
        "px,0,0) scaleX(" +
        (tabNavCurrentLinkTextPosition.width-30) * 0.01 +
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
})();