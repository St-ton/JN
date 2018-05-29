function Debug()
{
    debuglog('construct Debug');

    bindProtoOnHandlers(this);

    this.viewInfoTree = undefined;
}

Debug.prototype = {

    constructor: Debug,

    init: function()
    {
        debuglog('Debug init');

        installGuiElements(this, ['debugPageTree', 'debugTreeRefresh']);
    },

    onDebugTreeRefresh: function()
    {
        this.refresh();
    },

    createExpandFunc: function(vit)
    {
        return function(e) {
            $(e.target).closest('li').toggleClass('expanded');
            //vit.__expanded = !vit.__expanded;
            e.stopPropagation();
        };
    },

    expandClass: function(vit)
    {
        return '';//(vit.__expanded === true ? ' expanded' : '')
    },

    refresh: function()
    {
        var json = opc.page.toJSON(true);

        if(this.viewInfoTree === undefined) {
            this.viewInfoTree = json;
        }

        this.debugPageTree.empty().append(this.renderPage(json, this.viewInfoTree));
    },

    renderPage: function(json, vit)
    {
        return $('<ul>')
            .append(
                $('<li>')
                    .html('<b>Page ID</b>: <span class="string">"' + json.id + '"</span>')
            )
            .append(
                $('<li>')
                    .html('<b>Page URL</b>: <span class="string">"' + json.url + '"</span>')
            )
            .append(
                $('<li class="tree' + this.expandClass(vit.areas) + '">')
                    .html('<b>Page Areas</b>: ')
                    .append(this.renderAreas(json.areas, vit.areas))
                    .click(this.createExpandFunc(vit.areas))
            )
        ;
    },

    renderAreas: function(json, vit)
    {
        vit = vit || json;

        var res = $('<ul>');

        for (var areaId in json) {
            res.append(
                $('<li class="tree' + this.expandClass(vit[areaId]) + '">')
                    .text(areaId)
                    .append(this.renderArea(json[areaId], vit[areaId]))
                    .click(this.createExpandFunc(vit[areaId]))
            );
        }

        return res;
    },

    renderArea: function(json, vit)
    {
        vit = vit || json;

        var res = $('<ul>');

        for(var i=0; i<json.content.length; i++) {
            var portlet = json.content[i];
            res.append(this.renderPortlet(i, portlet, vit.content[i]));
        }

        return res;
    },

    renderPortlet: function(index, json, vit)
    {
        vit = vit || json;

        var res = $('<li class="tree' + this.expandClass(vit) + '">')
            .text(index + ': ' + json.title + ' (Class: ' + json.class + ')')
            .click(this.createExpandFunc(vit))
            .mouseover(function(e) {
                opc.iframe.setHovered(json.elm);
                e.stopPropagation();
            });

        var ul  = $('<ul>').appendTo(res);

        if(Object.keys(json.properties).length > 0) {
            ul.append(this.renderPortletProps(json.properties, vit.properties));
        } else {
            ul.append($('<li>No Properies</li>'))
        }

        if(Object.keys(json.subareas).length > 0) {
            ul.append(this.renderPortletSubareas(json.subareas, vit.subareas));
        } else {
            ul.append($('<li>No Subareas</li>'))
        }

        return res;
    },

    renderPortletProps: function(json, vit)
    {
        vit = vit || json;

        var res = $('<li class="tree' + this.expandClass(vit) + '">')
            .html('<b>Properties</b>: ')
            .click(this.createExpandFunc(vit));

        var ul  = $('<ul>');

        for(var propname in json) {
            if(propname !== '__expanded') {
                ul.append(this.renderProperty(propname, json[propname], vit[propname]));
            }
        }

        res.append(ul);
        return res;
    },

    renderProperty: function(name, val, vit)
    {
        vit = vit || val;

        var isArray     = Array.isArray(val);
        var isObject    = typeof val === 'object';
        var appendClass = isObject ? ' class="tree"' : '';
        var appendLabel = isArray ? '[...]' : isObject ? '{ ... }' : '';

        return $('<li' + appendClass + this.expandClass(vit) + '>')
            .text(name + ': ' + appendLabel)
            .append(this.renderPropVal(val, vit))
            .click(this.createExpandFunc(vit));
    },

    renderPropVal: function(val, vit)
    {
        var ul;

        if(typeof val === 'number') {
            return $('<span class="number">' + val + '</span>');
        } else if(typeof val === 'string') {
            return $('<span class="string">"' + val + '"</span>');
        } else if(Array.isArray(val)) {
            ul = $('<ul>');

            for(var i=0; i<val.length; i++) {
                ul.append(this.renderProperty(i, val[i], vit[i]));
            }

            return ul;
        } else {
            ul = $('<ul>');

            for(var propname in val) {
                ul.append(this.renderProperty(propname, val[propname], vit[propname]));
            }

            return ul;
        }
    },

    renderPortletSubareas: function(json, vit)
    {
        return $('<li class="tree' + this.expandClass(vit) + '">')
            .html('<b>Subareas</b>:')
            .append(this.renderAreas(json, vit))
            .click(this.createExpandFunc(vit));
    },
};