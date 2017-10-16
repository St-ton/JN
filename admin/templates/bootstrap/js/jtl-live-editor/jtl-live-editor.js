
function JtlLiveEditor(selector)
{
    this.selectedElm = null;
    this.draggedElm = null;
    this.targetElm = null;
    this.rootElm = $(selector);
    this.placeholderElm = $("<div>", { "class": "jle-placeholder" });
    this.selectedLabelElm = $("<div>", { "class": "jle-selected-label" });
    this.targetLabelElm = $("<div>", { "class": "jle-target-label" });

    this.rootElm.on("mouseover", this.onMouseOver.bind(this));
    this.rootElm.on("dragstart", this.onDragStart.bind(this));
    this.rootElm.on("dragend", this.onDragEnd.bind(this));
    this.rootElm.on("dragover", this.onDragOver.bind(this));
    this.rootElm.on("drop", this.onDrop.bind(this));
}

JtlLiveEditor.prototype.cleanUpDrag = function()
{
    this.placeholderElm.remove();
    this.targetLabelElm.remove();
    $(".jle-drop-target").removeClass("jle-drop-target");
    $(".jle-drop-before").removeClass("jle-drop-before");
    $(".jle-drop-after").removeClass("jle-drop-after");
    $(".jle-drop-left").removeClass("jle-drop-left");
    $(".jle-drop-right").removeClass("jle-drop-right");
    $(".jle-dragged").removeClass("jle-dragged");
}

JtlLiveEditor.prototype.onMouseOver = function(e)
{
    var elm = $(e.target);

    while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
        elm = elm.parent();
    }

    if(this.isSelectable(elm)) {
        if(this.selectedElm !== null && !this.selectedElm.is(elm)) {
            this.selectedElm.attr("contenteditable", "inherit");
            this.selectedElm.attr("draggable", "false");
            this.selectedElm.removeClass("jle-selected");
        }

        this.selectedElm = elm;
        //this.selectedElm.attr("contenteditable", "true");
        this.selectedElm.attr("draggable", "true");
        this.selectedElm.addClass("jle-selected");
        this.selectedElm.append(this.selectedLabelElm);
        this.selectedLabelElm.text(
            this.selectedElm.prop("tagName").toLowerCase() + "." +
            this.selectedElm.attr("class").split(" ").join(".")
        );
    }
};

JtlLiveEditor.prototype.onDragStart = function(e)
{
    console.log("dragstart", e);
    this.draggedElm = $(e.target);
    this.draggedElm.addClass('jle-dragged');

    // firefox needs this
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/html', this.draggedElm.innerHTML);
};

JtlLiveEditor.prototype.onDragEnd = function(e)
{
    console.log("dragend");
    this.cleanUpDrag();
};

JtlLiveEditor.prototype.onDragOver = function(e)
{
    console.log("dragover");
    var elm = $(e.target);

    var horiRatio = (e.clientX - elm.offset().left) / elm.outerWidth();
    var vertRatio = (e.clientY - elm.offset().top) / elm.outerHeight();

    $(".jle-drop-target").removeClass("jle-drop-target");
    $(".jle-drop-before").removeClass("jle-drop-before");
    $(".jle-drop-after").removeClass("jle-drop-after");
    $(".jle-drop-left").removeClass("jle-drop-left");
    $(".jle-drop-right").removeClass("jle-drop-right");

    this.targetElm = null;

    if(!isDescendant(elm, this.draggedElm)) {
        if(vertRatio < 0.33 || horiRatio < 0.33) {
            if(!elm.is(this.rootElm)) {
                this.targetElm = elm;
                this.placeholderElm.insertBefore(elm);
                elm.addClass(vertRatio < 0.33 ? "jle-drop-before" : "jle-drop-left");
            }
        }
        else if(
            vertRatio >= 0.33 && vertRatio <= 0.66 && horiRatio >= 0.33 && horiRatio <= 0.66
        ) {
            if(!this.draggedElm.is(elm)) {
                this.targetElm = elm;
                this.placeholderElm.appendTo(elm);
                elm.addClass("jle-drop-target");
            }
        }
        else if(vertRatio > 0.66 || horiRatio > 0.66) {
            if(!elm.is(this.rootElm)) {
                this.targetElm = elm;
                this.placeholderElm.insertAfter(elm);
                elm.addClass(vertRatio > 0.66 ? "jle-drop-after" : "jle-drop-right");
            }
        }
    }

    if(this.targetElm !== null) {
        this.targetElm.append(this.targetLabelElm);
        this.targetLabelElm.text(
            this.targetElm.prop("tagName").toLowerCase() + "." +
            this.targetElm.attr("class").split(" ").join(".")
        );
    }

    e.preventDefault();
};

JtlLiveEditor.prototype.onDrop = function(e)
{
    console.log("drop");
    var elm = $(e.target);

    this.placeholderElm.replaceWith(this.draggedElm);
};

JtlLiveEditor.prototype.isSelectable = function(elm)
{
    return !isInline(elm) && !elm.is(this.rootElm) && !elm.is(this.selectedLabelElm) &&
        !elm.is(this.targetLabelElm);
};

function isInline(elm)
{
    return elm.css("display") === "inline" || elm.css("display") === "inline-block";
}

function isDescendant(descendant, tree)
{
    return tree.has(descendant).length > 0;
}
