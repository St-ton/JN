class PageTree
{
    constructor(page, iframe)
    {
        bindProtoOnHandlers(this);

        this.page     = page;
        this.iframe   = iframe;
        this.selected = undefined;
    }

    init()
    {
        installGuiElements(this, ['pageTreeView']);
    }

    setSelected(portlet)
    {
        if(this.selected) {
            $(this.selected[0].treeItem).removeClass('selected');
        }

        this.selected = portlet;

        if(this.selected) {
            $(this.selected[0].treeItem).addClass('selected');
            this.expandTo(portlet);
        }
    }

    expandTo(portlet)
    {
        let treeItem = $(portlet[0].treeItem);

        while(treeItem.length > 0) {
            treeItem = treeItem.parent().closest('li');
            treeItem.addClass('expanded');
        }
    }

    render()
    {
        let rootAreas = this.page.rootAreas;
        let jq        = rootAreas.constructor;
        let ul        = $('<ul>');

        rootAreas.each((i, area) => {
            ul.append(this.renderArea(jq(area)));
        });

        this.pageTreeView.empty().append(ul);

        if(this.page.offscreenAreas.length) {
            ul = $('<ul>');

            this.page.offscreenAreas.each((i, area) => {
                ul.append(this.renderArea(jq(area)));
            });

            this.pageTreeView.append('<h4>Offscreen</h4>');
            this.pageTreeView.append(ul);
        }
    }

    renderBaseItem(text, click, cls = '')
    {
        let expander = $('<a href="#" class="item-expander">');
        let item     = $('<a href="#" class="item-label">');
        let copybtn  = $('<a href="#" class="item-copybtn">');
        let head     = $('<div class="item-head">')
        let li       = $('<li class="' + cls + '">');

        expander.append('<i class="fas fa-fw fa-chevron-right">');
        expander.append('<i class="fas fa-fw fa-chevron-down">');
        copybtn.append('<i class="far fa-fw fa-copy">');
        item.append(' ' + text);

        function expand(e) {
            e.preventDefault();
            li.toggleClass('expanded');
        }

        expander.on('click', expand);

        if(click) {
            item.dblclick(expand);
        }

        item.on('click', e => {
            e.preventDefault();

            if(click) {
                click();
            } else {
                li.toggleClass('expanded');
            }
        });

        head.append(expander).append(item).append(copybtn);
        return li.append(head);
    }

    renderArea(area, expanded)
    {
        let portlets = area.children('[data-portlet]');
        let jq       = area.constructor;
        let data     = area.data('area-id');
        let ul       = $('<ul>');
        let li       = this.renderBaseItem('' + data + '', null, 'area-item');

        expanded = expanded || false;

        portlets.each((i, portlet) => {
            portlet = jq(portlet);
            ul.append(this.renderPortlet(portlet));
        });

        if(portlets.length === 0) {
            li.addClass('leaf');
        } else if(expanded) {
            li.addClass('expanded');
        }

        area[0].treeItem = li[0];
        li[0].area       = area[0];

        return li.append(ul);
    }

    renderPortlet(portlet)
    {
        let subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));
        let jq       = portlet.constructor;
        let data     = portlet.data('portlet');
        let ul       = $('<ul>');

        let li = this.renderBaseItem(data.class, () => {
            this.iframe.setSelected(portlet);
        }, 'portlet-item');

        subareas.each((i, area) => {
            area = jq(area);
            ul.append(this.renderArea(area));
        });

        if(subareas.length === 0) {
            li.addClass('leaf');
        }

        portlet[0].treeItem = li[0];
        li[0].portlet       = portlet[0];

        return li.append(ul);
    }

    updateArea(area)
    {
        let treeItem = $(area[0].treeItem);
        let expanded = treeItem.hasClass('expanded');

        $(area[0].treeItem).replaceWith(this.renderArea(area, expanded));
    }
}
