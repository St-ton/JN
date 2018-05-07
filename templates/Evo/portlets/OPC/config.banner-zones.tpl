<div id="banner-zones-container" {*{if empty($instance->getProperty('zones'))}style="display:none"{/if}*}>
    <div class="row">
        <div class="col-xs-12">
            <div id="area_container" class="form-group">
                <div id="area_wrapper">
                    <img src="{$instance->getProperty('src')}" title="" id="clickarea" class="img-responsive"/>
                </div>
            </div>
            <div class="save_wrapper btn-group form-group">
                <input id="zones" type="hidden" name="zones" value="">
                <a class="btn btn-default" href="#" id="area_new"><i class="fa fa-share"></i> Neue Zone</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div id="area_editor" class="panel panel-default" style="display: none;">
                <div class="category first panel-heading">
                    <h3 class="panel-title">Einstellungen</h3>
                </div>
                <div id="settings" class="panel-body">
                    <div class="input-group form-group">
                                <span class="input-group-addon">
                                    <label for="title">Titel</label>
                                </span>
                        <input class="form-control" type="text" id="title" name="title" />
                    </div>
                    <div class="input-group form-group">
                                <span class="input-group-addon">
                                    <label for="desc">Beschreibung</label>
                                </span>
                        <textarea class="form-control" id="desc" name="desc"></textarea>
                    </div>
                    <div class="input-group form-group">
                                <span class="input-group-addon">
                                    <label for="url">Url</label>
                                </span>
                        <input class="form-control" type="text" id="url" name="url" />
                    </div>
                    <div class="input-group form-group">
                                <span class="input-group-addon">
                                    <label for="style">CSS-Klasse</label>
                                </span>
                        <input class="form-control" type="text" id="style" name="style" />
                    </div>
                    <div class="input-group form-group">
                                <span class="input-group-addon">
                                    <label for="article_name">Artikel</label>
                                </span>
                        <input type="hidden" name="article" id="article" value="{if isset($oBanner->kArtikel)}{$oBanner->kArtikel}{/if}" />
                        <input type="text" name="article_name" id="article_name" value="" class="form-control">
                        <input type="hidden" name="article_id" id="article_id" value="">
                    </div>
                    <input type="hidden" name="id" id="id" />
                    <div class="save_wrapper btn-group">
                        <a href="#" class="btn btn-default" id="article_browser">Artikel w&auml;hlen</a>
                        <a href="#" class="btn btn-default" id="article_unlink">Artikel L&ouml;sen</a>
                        <button type="button" class="btn btn-danger" id="remove"><i class="fa fa-trash"></i> Zone l&ouml;schen</button>
                        <a class="btn btn-primary" href="#" id="area_save"><i class="fa fa-save"></i> Zonen speichern</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../templates/Evo/portlets/OPC/config.banner-zones.js"></script>
    <style>
        #area_wrapper {
            position: relative;
            margin: 0 auto;
            text-align: left;
        }

        #area_wrapper img {
            /*position: absolute;*/
            top: 0; left: 0;
            z-index: 1;
            max-width: 100%;
        }

        #area_wrapper div.area {
            z-index: 2;
            position: absolute;
            background-color: #fff;
            /*border: 2px dashed #333;*/
            -moz-box-shadow: 0px 0px 20px rgba(000,000,000,0.7), inset 0px 0px 3px rgba(255,255,255,1);
            -webkit-box-shadow: 0px 0px 20px rgba(000,000,000,0.7), inset 0px 0px 3px rgba(255,255,255,1);
            -moz-box-shadow: 0px 0px 20px rgba(000,000,000,0.9), inset 0px 0px 3px rgba(255,255,255,1);
            -webkit-box-shadow: 0px 0px 20px rgba(000,000,000,0.9), inset 0px 0px 3px rgba(255,255,255,1);
        }

        #area_wrapper div.area.selected {
            background-color: #FFC299;
            /*border-color: #E05B02;*/
        }



        .ui-draggable { cursor: move; }
        .ui-resizable { position: relative;}
        .ui-resizable-handle { position: absolute;font-size: 0.1px;z-index: 99999; display: block; }
        .ui-resizable-disabled .ui-resizable-handle, .ui-resizable-autohide .ui-resizable-handle { display: none; }
        .ui-resizable-n { cursor: n-resize; height: 7px; width: 100%; top: -5px; left: 0; }
        .ui-resizable-s { cursor: s-resize; height: 7px; width: 100%; bottom: -5px; left: 0; }
        .ui-resizable-e { cursor: e-resize; width: 7px; right: -5px; top: 0; height: 100%; }
        .ui-resizable-w { cursor: w-resize; width: 7px; left: -5px; top: 0; height: 100%; }
        .ui-resizable-se { cursor: se-resize; width: 12px; height: 12px; right: 1px; bottom: 1px; }
        .ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: -5px; bottom: -5px; }
        .ui-resizable-nw { cursor: nw-resize; width: 9px; height: 9px; left: -5px; top: -5px; }
        .ui-resizable-ne { cursor: ne-resize; width: 9px; height: 9px; right: -5px; top: -5px;}

    </style>
    <script type="text/javascript">
        function kcfinderCallback(url) {
            console.log('callback');
//            $('#area_wrapper img').attr('src', url);
//            if ($('#preview-img-src').val().length !== 0 && str.indexOf("preview.banner") == -1){
//                $('#banner-zones-container').show();
//            }

        }

        $(function () {
            $.clickareas({
                'id': '#area_wrapper',
                'editor': '#area_editor',
                'save': '#area_save',
                'add': '#area_new',
                'info': '#area_info',
                'data': {$instance->getProperty('data')|@json_encode nofilter}
            });

            $('#article_unlink').click(function () {
                $('#article_id').val(0);
                $('#article_name').val('');
                return false;
            });

            enableTypeahead('#article_name', 'getProducts', 'cName', null, function (e, item) {
                $('#article_name').val(item.cName);
                $('#article_id').val(item.kArtikel);
            });
        });
    </script>
</div>