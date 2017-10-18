function JtlLiveEditor(selector, jleHost)
{
    jle = this;

    this.jleHost = jleHost;

    this.hoveredElm = null;
    this.selectedElm = null;
    this.draggedElm = null;
    this.targetElm = null;
    this.adjacentElm = null;
    this.adjacentDir = ''; // 'left', 'right', 'above', 'below'
    this.rootElm = $(selector);
    this.labelElm = $('<div>', { 'class': 'jle-label' }).appendTo('body').hide();
    this.pinbarElm = this.createPinbar().appendTo('body').hide();
    this.editingText = false;

    this.rootElm.on('mouseover', this.onMouseOver.bind(this));
    this.rootElm.on('click', this.onClick.bind(this));
    this.rootElm.on('dragstart', this.onDragStart.bind(this));
    this.rootElm.on('dragend', this.onDragEnd.bind(this));
    this.rootElm.on('dragover', this.onDragOver.bind(this));
    this.rootElm.on('drop', this.onDrop.bind(this));
    this.rootElm.on('keydown', this.onKeyDown.bind(this));
}

JtlLiveEditor.prototype.onMouseOver = function(e)
{
    var elm = $(e.target);

    while(!this.isSelectable(elm) && !elm.is(this.rootElm)) {
        elm = elm.parent();
    }

    if(this.isSelectable(elm)) {
        this.setHovered(elm);
    }
    else {
        this.setHovered();
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
    else {
        this.setSelected();
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
        var horiRatio = (e.clientX - adjacent.offset().left) / adjacent.innerWidth();
        var vertRatio = (e.clientY - adjacent.offset().top) / adjacent.innerHeight();

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

        var selectedElm = this.selectedElm;
        this.setSelected();
        this.setSelected(selectedElm);
    }
};

JtlLiveEditor.prototype.onDragEnd = function(e)
{
    console.log('dragend');

    this.cleanUpDrag();
};

JtlLiveEditor.prototype.onKeyDown = function(e)
{
    if(this.editingText) {
        if(e.key === 'Enter' && !e.shiftKey) {
            if(this.selectedElm[0].lastChild.nodeName !== 'BR') {
                this.selectedElm.append($('<br>'));
            }

            var sel = getSelection();
            var range = sel.getRangeAt(0);
            var br = document.createElement('br');
            
            range.deleteContents();
            range.insertNode(br);
            range.setStartAfter(br);
            range.setEndAfter(br);

            e.preventDefault();
        }
    }
};

JtlLiveEditor.prototype.onFocus = function(e)
{
    console.log('focus', this.selectedElm);

    this.editingText = true;
};

JtlLiveEditor.prototype.onBlur = function(e)
{
    console.log('blur', this.selectedElm);

    this.editingText = false;
};

JtlLiveEditor.prototype.onBold = function(e)
{
    document.execCommand('bold');
};

JtlLiveEditor.prototype.onItalic = function(e)
{
    document.execCommand('italic');
};

JtlLiveEditor.prototype.onTrash = function(e)
{
    if(this.selectedElm !== null) {
        this.selectedElm.remove();
        this.setSelected();
    }
};

JtlLiveEditor.prototype.onConfig = function(e)
{
    this.jleHost.showSettings(1);
};

JtlLiveEditor.prototype.setHovered = function(elm)
{
    elm = elm || null;

    if(this.hoveredElm !== null) {
        this.hoveredElm.removeClass('jle-hovered');
        this.hoveredElm.attr('draggable', 'false');
        this.labelElm.hide();
    }

    this.hoveredElm = elm;

    if(this.hoveredElm !== null) {
        this.hoveredElm.addClass('jle-hovered');
        this.hoveredElm.attr('draggable', 'true');
        var labelText = (
            this.hoveredElm.prop('tagName').toLowerCase() + "." +
            this.hoveredElm.attr('class').split(' ').join('.')
        );
        this.labelElm
            .text(labelText)
            .show()
            .css({
                left: elm.offset().left + 'px',
                top: elm.offset().top - this.labelElm.outerHeight() + 'px'
            })
    }
};

JtlLiveEditor.prototype.setSelected = function(elm)
{
    elm = elm || null;

    if(elm === null || !elm.is(this.selectedElm)) {
        if(this.selectedElm !== null) {
            this.selectedElm.removeClass('jle-selected');
            this.selectedElm.attr('contenteditable', 'false');
            this.pinbarElm.hide();
        }

        this.selectedElm = elm;

        if(this.selectedElm !== null) {
            this.selectedElm.addClass('jle-selected');
            this.selectedElm.attr('contenteditable', 'true');
            this.selectedElm.off('blur').on('blur', this.onBlur.bind(this));
            this.selectedElm.off('focus').on('focus', this.onFocus.bind(this));
            this.pinbarElm
                .show()
                .css({
                    left: elm.offset().left + elm.outerWidth() - this.pinbarElm.outerWidth() + 'px',
                    top: elm.offset().top - this.pinbarElm.outerHeight() + 'px'
                });
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

    if(this.adjacentElm !== null && this.adjacentDir !== '') {
        this.adjacentElm.addClass('jle-adjacent-' + dir);
    }
};

JtlLiveEditor.prototype.createPinbar = function()
{
    var pinbarElm = $('<div class="jle-pinbar btn-group">');

    pinbarElm.append(
        $('<button class="btn btn-default" id="jle-btn-bold"><i class="fa fa-bold"></i></button>')
            .click(this.onBold.bind(this))
    );
    pinbarElm.append(
        $('<button class="btn btn-default" id="jle-btn-italic"><i class="fa fa-italic"></i></button>')
            .click(this.onItalic.bind(this))
    );
    pinbarElm.append(
        $('<button class="btn btn-default" id="jle-btn-trash"><i class="fa fa-trash"></i></button>')
            .click(this.onTrash.bind(this))
    );
    pinbarElm.append(
        $('<button class="btn btn-default"><i class="fa fa-cog"></i></button>')
            .click(this.onConfig.bind(this))
    );

    return pinbarElm;
}

JtlLiveEditor.prototype.cleanUpDrag = function()
{
    this.setDragged();
    this.setDropTarget();
    this.setAdjacent();
};

JtlLiveEditor.prototype.isSelectable = function(elm)
{
    // return elm.is(this.rootElm);
    return !this.isInline(elm) && !elm.is(this.rootElm) && !elm.parent().is('.row');
    // && !elm.is(this.selectedLabelElm) && !elm.is(this.targetLabelElm);
};

JtlLiveEditor.prototype.isDropTarget = function(elm)
{
    return !this.isDescendant(elm, this.draggedElm) && (
        elm.is(this.rootElm) || elm.parent().is('.row')
    );
};

JtlLiveEditor.prototype.isInline = function(elm)
{
    return elm.css('display') === 'inline' || elm.css('display') === 'inline-block';
};

JtlLiveEditor.prototype.isDescendant = function(descendant, tree)
{
    return tree.has(descendant).length > 0;
};

JtlLiveEditor.prototype.toJson = function(elm)
{
    if(elm === undefined) {
        var result = [];

        for(var i=0; i<this.rootElm.length; i++) {
            result.push(
                this.toJson($(this.rootElm[i]))
            );
        }

        return result;
    }
    else {
        var result = { };
        var children = [];

        if(elm.hasClass('jle-editable')) {
            result.type = 'jle-editable';
            result.id = elm.attr('id');
        }

        if(elm.hasClass('jle-editable')) {
            result.children = [];

            elm.children().each(function(index, child) {
                result.children.push(child.outerHTML);
            });
        }
        else {

        }

        return result;
    }
};