function PageTree(page, iframe)
{
    bindProtoOnHandlers(this);

    this.page     = page;
    this.iframe   = iframe;
    this.selected = undefined;
}

PageTree.prototype = {

    constructor: PageTree,

    init: function()
    {
        installGuiElements(this, ['pageTreeView']);
    },

    setSelected: function(portlet)
    {
        if(this.selected) {
            $(this.selected[0].treeItem).removeClass('selected');
        }

        this.selected = portlet;

        if(this.selected) {
            $(this.selected[0].treeItem).addClass('selected');
            this.expandTo(portlet);
        }
    },

    expandTo: function(portlet)
    {
        var treeItem = $(portlet[0].treeItem);

        while(treeItem.length > 0) {
            treeItem = treeItem.parent().closest('li');
            treeItem.addClass('expanded');
        }
    },

    render: function()
    {
        debuglog('PageTree render');

        var rootAreas = this.page.rootAreas;
        var jq        = rootAreas.constructor;
        var ul        = $('<ul>');

        rootAreas.each(function(i, area)
        {
            area = jq(area);
            ul.append(this.renderArea(area));

        }.bind(this));

        this.pageTreeView.empty().append(ul);
    },

    renderBaseItem: function(text, click)
    {
        var expander = $('<a href="#">');
        var item     = $('<a href="#" class="item-label">');
        var li       = $('<li>');

        expander.append('<i class="fa fa-fw fa-chevron-right">');
        expander.append('<i class="fa fa-fw fa-chevron-down">');
        item.append(' ' + text);

        function expand(e) {
            e.preventDefault();
            li.toggleClass('expanded');
        }

        expander.click(expand);
        item.dblclick(expand);

        item.click(function(e) {
            e.preventDefault();

            if(click) {
                click();
            }
        });

        return li.append(expander).append(item);
    },

    renderArea: function(area, expanded)
    {
        var portlets = area.children('[data-portlet]');
        var jq       = area.constructor;
        var data     = area.data('area-id');
        var ul       = $('<ul>');
        var li       = this.renderBaseItem('Area: "' + data + '"');

        expanded = expanded || false;

        portlets.each(function(i, portlet)
        {
            portlet = jq(portlet);
            ul.append(this.renderPortlet(portlet));

        }.bind(this));

        if(portlets.length === 0) {
            li.addClass('leaf');
        }
        else if(expanded) {
            li.addClass('expanded');
        }

        area[0].treeItem = li[0];
        li[0].area       = area[0];

        return li.append(ul);
    },

    renderPortlet: function(portlet)
    {
        var subareas = portlet.find('.opc-area').not(portlet.find('[data-portlet] .opc-area'));
        var jq       = portlet.constructor;
        var data     = portlet.data('portlet');
        var ul       = $('<ul>');

        var li = this.renderBaseItem(data.class, function()
        {
            this.iframe.setSelected(portlet);

        }.bind(this));

        subareas.each(function(i, area)
        {
            area = jq(area);
            ul.append(this.renderArea(area));

        }.bind(this));

        if(subareas.length === 0) {
            li.addClass('leaf');
        }

        portlet[0].treeItem = li[0];
        li[0].portlet       = portlet[0];

        return li.append(ul);
    },

    updateArea: function(area)
    {
        var treeItem = $(area[0].treeItem);
        var expanded = treeItem.hasClass('expanded');

        $(area[0].treeItem).replaceWith(this.renderArea(area, expanded));
    },

};