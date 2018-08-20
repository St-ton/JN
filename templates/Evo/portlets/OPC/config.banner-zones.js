$.clickareas = function(options)
{
    $(function ()
    {
        var unique = 0;
        var clickarea = $('#clickarea');
        var factor = (clickarea.prop("naturalWidth") / clickarea.prop("width")).toFixed(2);
        var editor = $(options.editor);
        var areaWrapper = $(options.id);
        var width = parseInt(areaWrapper.find('img').attr('width') || 0);
        var height = parseInt(areaWrapper.find('img').attr('height') || 0);

        if (width === 0 && height === 0) {
            areaWrapper.find('img').bind('load', function () {
                loadImage();
            });
        } else {
            loadImage();
        }

        $(options.remove).click(function() {
            var id = parseInt($('#area_id').val() || 0);
            var area = getData(id);

            areaWrapper.find('.area').each(function(idx, area) {
                if ($(this).data('ref') === id) {
                    $(this).remove();
                }
            });

            if (area) {
                options.oArea_arr.splice(options.oArea_arr.indexOf(area), 1);
            }

            hideEditor();

            return false;
        });

        $(options.save).click(function() {
            saveEditor();

            var oArea_arr = JSON.stringify(options.oArea_arr);

            $('#zones').val(oArea_arr);

            $(options.info)
                .text('Zonen wurden erfolgreich gespeichert')
                .fadeIn()
                .delay(1500)
                .fadeOut();

            return false;
        });

        $(options.add).click(function() {
            factor = (clickarea.prop("naturalWidth") / clickarea.prop("width")).toFixed(2);

            var item = {
                oCoords: {
                    w: Math.round(100 * factor),
                    h: Math.round(100 * factor),
                    x: Math.round(15 * factor),
                    y: Math.round(15 * factor)
                },
                cBeschreibung: '',
                cTitel: '',
                cUrl: '',
                kImageMap: options.kImageMap,
                kImageMapArea: 0,
                oArtikel: null,
                kArtikel: 0,
                cStyle: '',
            };

            if (options.oArea_arr === undefined || options.oArea_arr === null){
                options.oArea_arr = [];
            }

            options.oArea_arr.push(item);
            addAreaItem(item, true);

            return false;
        });

        areaWrapper.click(function() {
            releaseAreas();
        });

        function loadImage()
        {
            console.log('loadImage');
            width = areaWrapper.find('img').attr('width');
            height = areaWrapper.find('img').attr('height');

            areaWrapper.css({
                'width' : width,
                'height' : height,
            });

            setTimeout(function() {
                options.oArea_arr.forEach(function(item) {
                    addAreaItem(item, false);
                });
            }, 1000);
        }

        function releaseAreas()
        {
            console.log('releaseAreas');
            areaWrapper.find('.area').each(function(idx, area) {
                $(area).removeClass('selected');
                saveEditor();
            });

            hideEditor();
        }

        function getData(id)
        {
            for (var i=0; i<options.oArea_arr.length; i++) {
                var data = options.oArea_arr[i];

                if (data.uid === id) {
                    return data;
                }
            }

            return false;
        }

        function hideEditor()
        {
            editor.hide();
        }

        function saveEditor()
        {
            console.log('saveEditor');
            var id = parseInt($('#area_id').val() || 0);

            for (var i=0; i<options.oArea_arr.length; i++) {
                var item = options.oArea_arr[i];

                if (item.uid === id) {
                    if(!item.oArtikel) {
                        item.oArtikel = {cName: ''};
                    }

                    item.cTitel = $('#area_title').val();
                    item.cBeschreibung = $('#area_desc').val();
                    item.cUrl = $('#area_url').val();
                    item.kArtikel = $('#article_id').val();
                    item.oArtikel.cName = $('#article_name').val();
                    item.cStyle = $('#area_style').val();

                    break;
                }
            }
        }

        function selectArea(area)
        {
            console.log('selectArea');
            var id = $(area).data('ref');
            var data = getData(id);

            releaseAreas();
            $(area).addClass('selected');

            if (data) {
                $('#area_title').val(data.cTitel);
                $('#area_desc').val(data.cBeschreibung);
                $('#area_url').val(data.cUrl);
                $('#article_id').val(data.kArtikel);
                $('#article_name').val(data.oArtikel ? data.oArtikel.cName : '');
                $('#area_style').val(data.cStyle);
                $('#area_id').val(id);
                editor.show();
            }
        }

        function addAreaItem(item, select)
        {
            console.log('addAreaItem');
            factor = (clickarea.prop("naturalWidth") / clickarea.prop("width")).toFixed(2);
            item.uid = ++unique;

            var area = $('<div>')
                .css({
                    'width': Math.round(item.oCoords.w / factor),
                    'height': Math.round(item.oCoords.h / factor),
                    'left': Math.round(item.oCoords.x / factor),
                    'top': Math.round( item.oCoords.y / factor),
                    'opacity': 0.6,
                    'z-index': item.uid,
                    'position': 'absolute',
                })
                .attr({
                    'class': 'area',
                    //'ref': item.uid,
                })
                .data('ref', item.uid)
                .prependTo(areaWrapper);

            var bResize = false;

            area.resizable({
                handles: 'all',
                containment: 'parent',
                start: function(event, ui) {
                    bResize = true;
                    selectArea(this);
                },
                stop: function(event, ui) {
                    var id = $(this).data('ref');
                    var data = getData(id);

                    bResize = false;

                    if (data) {
                        var o_height = Math.round(areaWrapper.height() * factor);
                        var o_width = Math.round(areaWrapper.width() * factor);

                        data.oCoords.x = Math.round(ui.position.left * factor);
                        data.oCoords.y = Math.round(ui.position.top * factor);
                        data.oCoords.w = Math.round(ui.size.width * factor);
                        data.oCoords.h = Math.round(ui.size.height * factor);

                        if (ui.size.height > o_height - ui.position.top) {
                            data.oCoords.h = Math.round((o_height - ui.position.top) * factor);
                        }

                        if (ui.size.width > o_width - ui.position.left) {
                            data.oCoords.w = Math.round((o_width - ui.position.left) * factor);
                        }
                    }
                },
            });

            area.draggable({
                containment: 'parent',
                start: function(event, ui) {
                    selectArea(this);
                },
                stop: function(event, ui) {
                    var id = $(this).data('ref');
                    var data = getData(id);

                    if (data) {
                        data.oCoords.x = Math.round(ui.position.left * factor);
                        data.oCoords.y = Math.round(ui.position.top * factor);
                    }
                }
            });

            area.click(function(event) {
                event.stopPropagation();

                if ($(area).hasClass('selected')) {
                    if (!bResize) {
                        releaseAreas();
                    }
                }
                else {
                    selectArea(this);
                }
            });

            if (select) {
                selectArea(area);
            }
        }
    });
};