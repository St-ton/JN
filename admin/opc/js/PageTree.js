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
        var treeItem = $(portlet[0].treeItem);

        while(treeItem.length > 0) {
            treeItem = treeItem.parent().closest('li');
            treeItem.addClass('expanded');
        }
    }

    render()
    {
        var rootAreas = this.page.rootAreas;
        var jq        = rootAreas.constructor;
        var ul        = $('<ul>');

        rootAreas.each((i, area) => {
            ul.append(this.renderArea(jq(area)));
        });

        this.pageTreeView.empty().append(ul);
    }

    renderBaseItem(text, click, cls = '')
    {
        var expander = $('<a href="#" class="item-expander">');
        var item     = $('<a href="#" class="item-label ' + cls + '">');
        var li       = $('<li>');

        expander.append('<i class="fa fa-fw fa-chevron-right">');
        expander.append('<i class="fa fa-fw fa-chevron-down">');
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

        return li.append(expander).append(item);
    }

    renderArea(area, expanded)
    {
        var portlets = area.children('[data-portlet]');
        var jq       = area.constructor;
        var data     = area.data('area-id');
        var ul       = $('<ul>');
        var li       = this.renderBaseItem('' + data + '', null, 'area-item');

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
        var subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));
        var jq       = portlet.constructor;
        var data     = portlet.data('portlet');
        var ul       = $('<ul>');

        var li = this.renderBaseItem(data.class, () => {
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
        var treeItem = $(area[0].treeItem);
        var expanded = treeItem.hasClass('expanded');

        $(area[0].treeItem).replaceWith(this.renderArea(area, expanded));
    }
}
