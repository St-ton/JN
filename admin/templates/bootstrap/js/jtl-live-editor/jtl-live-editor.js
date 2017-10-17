
function JtlLiveEditor(selector)
{
    this.hoveredElm = null;
    this.selectedElm = null;
    this.draggedElm = null;
    this.targetElm = null;
    this.adjacentElm = null;
    this.adjacentDir = ''; // 'left', 'right', 'above', 'below'
    this.rootElm = $(selector);
    this.pinbarElm = $('<div>FOO</div>', { 'class': 'jle-pinbar' });

    // this.selectedLabelElm = $("<div>", { "class": "jle-selected-label" });
    // this.targetLabelElm = $("<div>", { "class": "jle-target-label" });

    this.rootElm.on('mouseover', this.onMouseOver.bind(this));
    this.rootElm.on('click', this.onClick.bind(this));
    this.rootElm.on('dragstart', this.onDragStart.bind(this));
    this.rootElm.on('dragend', this.onDragEnd.bind(this));
    this.rootElm.on('dragover', this.onDragOver.bind(this));
    this.rootElm.on('drop', this.onDrop.bind(this));
}

JtlLiveEditor.prototype.onMouseOver = function(e)
{
    var elm = $(e.target);

    this.setHovered();

    while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
        elm = elm.parent();
    }

    if(this.isSelectable(elm)) {
        this.setHovered(elm);
        // if(this.selectedElm !== null && !this.selectedElm.is(elm)) {
        //     this.selectedElm.attr("contenteditable", "inherit");
        //     this.selectedElm.attr("draggable", "false");
        //     this.selectedElm.removeClass("jle-selected");
        // }
        //
        // this.selectedElm = elm;
        // //this.selectedElm.attr("contenteditable", "true");
        // this.selectedElm.attr("draggable", "true");
        // this.selectedElm.addClass("jle-selected");
        // //this.selectedElm.append(this.selectedLabelElm);
        // this.selectedLabelElm.text(
        //     this.selectedElm.prop("tagName").toLowerCase() + "." +
        //     this.selectedElm.attr("class").split(" ").join(".")
        // );
    }
};

JtlLiveEditor.prototype.onClick = function(e)
{
    var elm = $(e.target);

    while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
        elm = elm.parent();
    }

    if(this.isSelectable(elm)) {
        this.setSelected(elm);
    }
};

JtlLiveEditor.prototype.onDragStart = function(e)
{
    console.log('dragstart', e);

    var elm = $(e.target);

    this.setDragged(elm);

    // firefox needs this
    e.originalEvent.dataTransfer.effectAllowed = 'move';
    e.originalEvent.dataTransfer.setData('text/html', '');
};

JtlLiveEditor.prototype.onDragOver = function(e)
{
    console.log('dragover', e);

    var elm = $(e.target);
    var adjacent = null;
    var dir = '';

    while(!this.isDropTarget(elm)) {
        adjacent = elm;
        elm = elm.parent();
    }

    if(adjacent !== null) {
        var horiRatio = (e.clientX - adjacent.offset().left) / adjacent.outerWidth();
        var vertRatio = (e.clientY - adjacent.offset().top) / adjacent.outerHeight();

        if(vertRatio < 0.33) {
            dir = 'above';
        }
        else if(vertRatio > 0.66) {
            dir = 'below';
        }
        else if(horiRatio < 0.33) {
            dir = 'left';
        }
        else if(horiRatio > 0.66) {
            dir = 'right';
        }
    }

    this.setAdjacent(adjacent, dir);
    this.setDropTarget(elm);

    e.preventDefault();

    // if(elm.is(this.rootElm)) {
    //     this.setDropTarget(elm);
    // }
    // else if(this.isDropTarget(elm)) {
    //
    // }

    // var horiRatio = (e.clientX - elm.offset().left) / elm.outerWidth();
    // var vertRatio = (e.clientY - elm.offset().top) / elm.outerHeight();
    //
    // if(vertRatio < 0.33 || horiRatio < 0.33) {
    // }
    // else if(
    //     vertRatio >= 0.33 && vertRatio <= 0.66 && horiRatio >= 0.33 && horiRatio <= 0.66
    // ) {
    //     console.log('joo');
    //     if(this.isDropTarget(elm)) {
    //         console.log('joo');
    //         e.preventDefault();
    //     }
    // }
    // else if(vertRatio > 0.66 || horiRatio > 0.66) {
    // }

    // console.log("dragover");
    // var elm = $(e.target);
    //
    // var horiRatio = (e.clientX - elm.offset().left) / elm.outerWidth();
    // var vertRatio = (e.clientY - elm.offset().top) / elm.outerHeight();
    //
    // $(".jle-drop-target").removeClass("jle-drop-target");
    // $(".jle-drop-before").removeClass("jle-drop-before");
    // $(".jle-drop-after").removeClass("jle-drop-after");
    // $(".jle-drop-left").removeClass("jle-drop-left");
    // $(".jle-drop-right").removeClass("jle-drop-right");
    //
    // this.targetElm = null;
    //
    // if(!isDescendant(elm, this.draggedElm)) {
    //     if(vertRatio < 0.33 || horiRatio < 0.33) {
    //         if(!elm.is(this.rootElm)) {
    //             this.targetElm = elm;
    //             this.placeholderElm.insertBefore(elm);
    //             elm.addClass(vertRatio < 0.33 ? "jle-drop-before" : "jle-drop-left");
    //         }
    //     }
    //     else if(
    //         vertRatio >= 0.33 && vertRatio <= 0.66 && horiRatio >= 0.33 && horiRatio <= 0.66
    //     ) {
    //         if(!this.draggedElm.is(elm)) {
    //             this.targetElm = elm;
    //             this.placeholderElm.appendTo(elm);
    //             elm.addClass("jle-drop-target");
    //         }
    //     }
    //     else if(vertRatio > 0.66 || horiRatio > 0.66) {
    //         if(!elm.is(this.rootElm)) {
    //             this.targetElm = elm;
    //             this.placeholderElm.insertAfter(elm);
    //             elm.addClass(vertRatio > 0.66 ? "jle-drop-after" : "jle-drop-right");
    //         }
    //     }
    // }
    //
    // if(this.targetElm !== null) {
    //     //this.targetElm.append(this.targetLabelElm);
    //     this.targetLabelElm.text(
    //         this.targetElm.prop("tagName").toLowerCase() + "." +
    //         this.targetElm.attr("class").split(" ").join(".")
    //     );
    // }
    //
    // e.preventDefault();
};

JtlLiveEditor.prototype.onDrop = function(e)
{
    console.log('drop');

    if(this.targetElm !== null) {
        if(this.adjacentElm !== null && this.adjacentDir !== '') {
            if(this.adjacentDir === 'left' || this.adjacentDir === 'above') {
                this.draggedElm.insertBefore(this.adjacentElm);
            }
            else if(this.adjacentDir === 'right' || this.adjacentDir === 'below') {
                this.draggedElm.insertAfter(this.adjacentElm);
            }
        }
        else {
            this.draggedElm.appendTo(this.targetElm);
        }
    }

    // console.log("drop");
    // var elm = $(e.target);
    //
    // this.placeholderElm.replaceWith(this.draggedElm);
};

JtlLiveEditor.prototype.onDragEnd = function(e)
{
    console.log('dragend');

    this.cleanUpDrag();
};

JtlLiveEditor.prototype.setHovered = function(elm)
{
    elm = elm || null;

    if(this.hoveredElm !== null) {
        this.hoveredElm.removeClass('jle-hovered');
        this.hoveredElm.attr('draggable', 'false');
    }

    this.hoveredElm = elm;

    if(this.hoveredElm !== null) {
        this.hoveredElm.addClass('jle-hovered');
        this.hoveredElm.attr('draggable', 'true');
    }
};

JtlLiveEditor.prototype.setSelected = function(elm)
{
    elm = elm || null;

    if(!elm.is(this.selectedElm)) {
        if(this.selectedElm !== null) {
            this.selectedElm.removeClass('jle-selected');
            this.selectedElm.attr('contenteditable', 'false');
        }

        this.selectedElm = elm;

        if(this.selectedElm !== null) {
            this.selectedElm.addClass('jle-selected');
            this.selectedElm.attr('contenteditable', 'true');
        }
    }
};

JtlLiveEditor.prototype.setDragged = function(elm)
{
    elm = elm || null;

    if(this.draggedElm !== null) {
        this.draggedElm.removeClass('jle-dragged');
    }

    this.draggedElm = elm;

    if(this.draggedElm !== null) {
        this.draggedElm.addClass('jle-dragged');
    }
};

JtlLiveEditor.prototype.setDropTarget = function(elm)
{
    elm = elm || null;

    if(this.targetElm !== null) {
        this.targetElm.removeClass('jle-droptarget');
    }

    this.targetElm = elm;

    if(this.targetElm !== null) {
        this.targetElm.addClass('jle-droptarget');
    }
};

JtlLiveEditor.prototype.setAdjacent = function(elm, dir)
{
    elm = elm || null;
    dir = dir || '';

    if(this.adjacentElm !== null) {
        this.adjacentElm.removeClass('jle-adjacent-left');
        this.adjacentElm.removeClass('jle-adjacent-right');
        this.adjacentElm.removeClass('jle-adjacent-above');
        this.adjacentElm.removeClass('jle-adjacent-below');
    }

    this.adjacentElm = elm;
    this.adjacentDir = dir;

    if(this.adjacentElm !== null) {
        this.adjacentElm.addClass('jle-adjacent-' + dir);
    }
};

JtlLiveEditor.prototype.cleanUpDrag = function()
{
    this.setDragged();
    this.setDropTarget();
    this.setAdjacent();
    // this.placeholderElm.remove();
    // this.targetLabelElm.remove();
    // $(".jle-drop-target").removeClass("jle-drop-target");
    // $(".jle-drop-before").removeClass("jle-drop-before");
    // $(".jle-drop-after").removeClass("jle-drop-after");
    // $(".jle-drop-left").removeClass("jle-drop-left");
    // $(".jle-drop-right").removeClass("jle-drop-right");
    // $(".jle-dragged").removeClass("jle-dragged");
};

JtlLiveEditor.prototype.isSelectable = function(elm)
{
    // return elm.is(this.rootElm);
    return !this.isInline(elm) && !elm.is(this.rootElm);
    // && !elm.is(this.selectedLabelElm) && !elm.is(this.targetLabelElm);
};

JtlLiveEditor.prototype.isDropTarget = function(elm)
{
    return elm.is(this.rootElm);//!isInline(elm);
};

JtlLiveEditor.prototype.isInline = function(elm)
{
    return elm.css('display') === 'inline' || elm.css('display') === 'inline-block';
};
//
// function isDescendant(descendant, tree)
// {
//     return tree.has(descendant).length > 0;
// }
