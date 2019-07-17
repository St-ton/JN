{$src = $instance->getProperty($propdesc.srcProp)}

<input type="hidden" id="config-{$propname}" name="{$propname}" value="{$propval|json_encode|htmlentities}"
       data-prop-type="json">

<div {if empty($src)}style="display: none"{/if} id="banner-editor-{$propname}">
    <div class="form-group">
        <label for="config-{$propname}">{$propdesc.label}</label>
        <div style="position: relative">
            <img src="{$src}" alt="Banner Zones"
                 id="banner-image-{$propname}" class="img-fluid">
            <div id="banner-zones-{$propname}" class="banner-zones"></div>
        </div>
    </div>

    <div class="form-group">
        <button type="button" class="opc-btn-primary opc-small-btn" id="banner-add-zone">{__('zoneNew')}</button>
        <button type="button" class="opc-btn-secondary opc-small-btn" id="banner-del-zone" style="display: none">
            {__('zoneDelete')}
        </button>
    </div>

    <div id="zone-props-{$propname}" style="display: none">
        <input type="text" class="form-control" id="zone-title-{$propname}" placeholder="{__('title')}">
        <textarea class="form-control" id="zone-desc-{$propname}" placeholder="{__('description')}"></textarea>
        <input type="text" class="form-control" id="zone-url-{$propname}" placeholder="{__('url')}">
        <input type="text" class="form-control" id="zone-class-{$propname}" placeholder="{__('cssClass')}">
        <div class="input-group">
            <input type="text" class="form-control" id="zone-product-{$propname}" placeholder="{__('products')}">
            <div class="input-group-append">
                <span class="input-group-text">
                    <button type="button" class="btn btn-sm btn-danger" id="banner-del-product">
                        <i class="fa fa-times"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>
</div>

<div id="banner-zone-blueprint-{$propname}" class="banner-zone" style="display: none;">
    <div id="banner-zone-resizer-blueprint-{$propname}"
         class="zone-resizer"></div>
</div>

<script>
    (function () {
        var nextZoneId    = 0;
        var hiddenInput   = $('#config-{$propname}');
        var bannerEditor  = $('#banner-editor-{$propname}');
        var bannerImg     = $('#banner-image-{$propname}');
        var bannerZones   = $('#banner-zones-{$propname}');
        var zoneBlueprint = $('#banner-zone-blueprint-{$propname}');
        var zoneProps     = $('#zone-props-{$propname}');
        var zoneTitle     = $('#zone-title-{$propname}');
        var zoneDesc      = $('#zone-desc-{$propname}');
        var zoneUrl       = $('#zone-url-{$propname}');
        var zoneClass     = $('#zone-class-{$propname}');
        var zoneProduct   = $('#zone-product-{$propname}');
        var dragging      = false;
        var resizing      = false;
        var editorw       = 0;
        var editorh       = 0;
        var startx        = 0;
        var starty        = 0;
        var draggedZone   = null;
        var selectedZone  = null;
        var startLeft     = 0;
        var startTop      = 0;
        var startWidth    = 0;
        var startHeight   = 0;

        deserializeZones().forEach(function(zoneData) {
            addZone(zoneData);
        });

        function delZone()
        {
            selectedZone.remove();
            zoneProps.hide();
            $('#banner-del-zone').hide();
            serializeZones();
        }

        function addZone(data = null)
        {
            var newZone = zoneBlueprint.clone();
            var resizer = newZone.find('#banner-zone-resizer-blueprint-{$propname}');

            var zoneData = data || {
                zoneId: nextZoneId,
                title: '',
                desc: '',
                url: '',
                class: '',
                productId: 0,
                productName: '',
                left: 50,
                top: 50,
                width: 25,
                height: 25,
            };

            if (data === null) {
                nextZoneId++;
            }

            newZone.show().appendTo(bannerZones);

            newZone
                .attr('id', '')
                .data('zone', zoneData)
                .css({
                    left:   zoneData.left + '%',
                    top:    zoneData.top + '%',
                    width:  zoneData.width + '%',
                    height: zoneData.height + '%',
                })
                .on('mousedown', function(e) {
                    dragging    = true;
                    startx      = e.clientX;
                    starty      = e.clientY;
                    editorw     = bannerZones.width();
                    editorh     = bannerZones.height();
                    draggedZone = newZone;
                    startLeft   = parseFloat(draggedZone[0].style.left);
                    startTop    = parseFloat(draggedZone[0].style.top);
                    selectZone(newZone);

                    e.preventDefault();
                });

            resizer
                .attr('id', '')
                .on('mousedown', function(e) {
                    resizing    = true;
                    startx      = e.clientX;
                    starty      = e.clientY;
                    draggedZone = newZone;
                    editorw     = bannerZones.width();
                    editorh     = bannerZones.height();
                    startWidth  = parseFloat(draggedZone[0].style.width);
                    startHeight = parseFloat(draggedZone[0].style.height);
                    selectZone(newZone);

                    e.preventDefault();
                    e.stopPropagation();
                });

            if (data === null) {
                serializeZones();
                selectZone(newZone);
            }
        }

        function selectZone(zone)
        {
            var zoneData = zone.data('zone');

            selectedZone = zone;
            bannerZones.find('.selected').removeClass('selected');
            selectedZone.addClass('selected');
            zoneProduct.val(zoneData.productName);
            zoneTitle.val(zoneData.title);
            zoneDesc.val(zoneData.desc);
            zoneUrl.val(zoneData.url);
            zoneClass.val(zoneData.class);
            zoneProps.show();
            $('#banner-del-zone').show();
        }

        function moveSelected(newx, newy)
        {
            var zoneData = draggedZone.data('zone');
            var curw = parseFloat(draggedZone[0].style.width);
            var curh = parseFloat(draggedZone[0].style.height);

            newx = Math.max(0, Math.min(100 - curw, newx));
            newy = Math.max(0, Math.min(100 - curh, newy));

            draggedZone[0].style.left = newx + "%";
            draggedZone[0].style.top  = newy + "%";

            zoneData.left = newx;
            zoneData.top  = newy;

            draggedZone.data('zone', zoneData);
            serializeZones();
        }

        function resizeSelected(neww, newh)
        {
            var zoneData = draggedZone.data('zone');
            var curx = parseFloat(draggedZone[0].style.left);
            var cury = parseFloat(draggedZone[0].style.top);

            neww = Math.max(5, Math.min(100 - curx, neww));
            newh = Math.max(5, Math.min(100 - cury, newh));

            draggedZone[0].style.width  = neww + "%";
            draggedZone[0].style.height = newh + "%";

            zoneData.width  = neww;
            zoneData.height = newh;

            draggedZone.data('zone', zoneData);
            serializeZones();
        }

        function serializeZones()
        {
            var zonesData = [];

            bannerZones.children().each(function(i, zone) {
                zonesData.push($(zone).data('zone'));
            });

            hiddenInput.val(JSON.stringify(zonesData));
        }

        function deserializeZones()
        {
            return JSON.parse(hiddenInput.val());
        }

        function delProduct()
        {
            var zoneData = selectedZone.data('zone');

            zoneProduct.val('');
            zoneData.productId   = 0;
            zoneData.productName = '';
            selectedZone.data('zone', zoneData);
            serializeZones();
        }

        zoneTitle.on('input', function() { changeZoneProp('title', $(this).val()); });
        zoneDesc.on('input', function() { changeZoneProp('desc', $(this).val()); });
        zoneUrl.on('input', function() { changeZoneProp('url', $(this).val()); });
        zoneClass.on('input', function() { changeZoneProp('class', $(this).val()); });

        function changeZoneProp(name, val)
        {
            var zoneData = selectedZone.data('zone');
            zoneData[name] = val;
            selectedZone.data('zone', zoneData);
            serializeZones();
        }

        enableTypeahead('#zone-product-{$propname}', 'getProducts', 'cName', null, function (e, item) {
            var zoneData = selectedZone.data('zone');

            zoneData.productId   = item.kArtikel;
            zoneData.productName = item.cName;
            selectedZone.data('zone', zoneData);
            serializeZones();
        });

        opc.setImageSelectCallback(function (url)
        {
            bannerEditor.show();
            bannerImg.attr('src', url);
        });

        $(document)
            .on('mouseup', function(e) {
                dragging = false;
                resizing = false;
            });

        $(document)
            .on('mousemove', function(e) {
                if (dragging || resizing) {
                    let deltax        = e.clientX - startx;
                    let deltay        = e.clientY - starty;
                    let deltaxpercent = deltax * 100 / editorw;
                    let deltaypercent = deltay * 100 / editorh;

                    if (dragging) {
                        moveSelected(startLeft + deltaxpercent, startTop + deltaypercent);
                        e.preventDefault();
                    } else if(resizing) {
                        resizeSelected(startWidth + deltaxpercent, startHeight + deltaypercent);
                        e.preventDefault();
                    }
                }
            });

        $('#banner-add-zone').on('click', function() { addZone(); });
        $('#banner-del-zone').on('click', delZone);
        $('#banner-del-product').on('click', delProduct)
    })();
</script>