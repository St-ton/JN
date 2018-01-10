<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="title">Titel</label>
                    <input type="text"  id="video-title" name="video-title" class="form-control" value="{$properties['video-title']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="class">Class</label>
                    <input type="text"  id="video-class" name="attr[class]" class="form-control" value="{$properties.attr['class']}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <label for="video-vendor">Anbieter</label>
                <div class="radio" id="video-vendor">
                    <label class="radio-inline">
                        <input type="radio" name="video-vendor" id="video-vendor-0" value="youtube"{if $properties['video-vendor'] === 'youtube'} checked="checked"{/if}> YouTube
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="video-vendor" id="video-vendor-1" value="vimeo"{if $properties['video-vendor'] === 'vimeo'} checked="checked"{/if}> Vimeo
                    </label>
                </div>
            </div>
        </div>
        <div class="well" id="youtube-container"{if $properties['video-vendor'] !== 'youtube'} style="display:none;"{/if}>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="video-yt-id">VideoID</label>
                        <input type="text" class="form-control" id="video-yt-id" name="video-yt-id" placeholder="xITQHgJ3RRo" value="{$properties['video-yt-id']}">
                        <span class="help-block">Bitte nur die ID des Videos eingeben. Bsp.: xITQHgJ3RRo</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <label for="video-yt-width">Abmessung</label>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-yt-responsive" name="video-yt-responsive" value="1" {if $properties['video-yt-responsive'] === '1'} checked="checked"{/if}> Größe an vorhandenen Platz anpassen?
                        </label>
                    </div>
                    <div class="form-group form-inline">
                        <div class="form-group">
                            <input type="number" class="form-control" id="video-yt-width" name="video-yt-width" value="{$properties['video-yt-width']}" {if $properties['video-yt-responsive'] === '1'} disabled{/if}>
                        </div>
                        x
                        <div class="form-group">
                            <input type="number" class="form-control" id="video-yt-height" name="video-yt-height" value="{$properties['video-yt-height']}" {if $properties['video-yt-responsive'] === '1'} disabled{/if}>
                        </div>
                        Pixel
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="video-yt-start">Start</label>
                        <input type="text"  id="video-yt-start" name="video-yt-start" class="form-control" value="{$properties['video-yt-start']}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="video-yt-end">Ende</label>
                        <input type="text"  id="video-yt-end" name="video-yt-end" class="form-control" value="{$properties['video-yt-end']}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-yt-autoplay" name="video-yt-autoplay" value="1" {if $properties['video-yt-autoplay'] === '1'} checked="checked"{/if}> Video automatisch starten?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-yt-controls" name="video-yt-controls" value="0" {if $properties['video-yt-controls'] === '0'} checked="checked"{/if}> Kontrollen ausblenden?
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-yt-loop" name="video-yt-loop" value="1" {if $properties['video-yt-loop'] === '1'} checked="checked"{/if}> Video nach Ablauf wiederholen?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-yt-rel" name="video-yt-rel" value="0" {if $properties['video-yt-rel'] === '0'} checked="checked"{/if}> keine ähnliche Videos nach Ablauf anzeigen?
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <label for="video-yt-color">Farbe</label>
                    <div class="radio" id="video-yt-color">
                        <label class="radio-inline">
                            <input type="radio" name="video-yt-color" id="video-yt-color-0" value="red"{if $properties['video-yt-color'] === 'red'} checked="checked"{/if}> Rot
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="video-yt-color" id="video-yt-color-1" value="white"{if $properties['video-yt-color'] === 'white'} checked="checked"{/if}> Weiß
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="video-yt-playlist">Playlist</label>
                        <input type="text"  id="video-yt-playlist" name="video-yt-playlist" class="form-control" value="{$properties['video-yt-playlist']}">
                        <span class="help-block">Geben Sie die Video-IDs durch Komma getrennt ein . Bsp.: xITQHgJ3RRo,sNYv0JgrUlw</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="well" id="vimeo-container"{if $properties['video-vendor'] !== 'vimeo'} style="display:none;"{/if}>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label for="video-vim-id">VideoID</label>
                        <input type="text" class="form-control" id="video-vim-id" name="video-vim-id" placeholder="" value="{$properties['video-vim-id']}">
                        <span class="help-block">Bitte nur die ID des Videos eingeben. Bsp.: 239593389</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <label for="video-vim-width">Abmessung</label>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-vim-responsive" name="video-vim-responsive" value="1" {if $properties['video-vim-responsive'] === '1'} checked="checked"{/if}> Größe an vorhandenen Platz anpassen?
                        </label>
                    </div>
                    <div class="form-group form-inline">
                        <div class="form-group">
                            <input type="number" class="form-control" id="video-vim-width" name="video-vim-width" value="{$properties['video-vim-width']}" {if $properties['video-vim-responsive'] === '1'} disabled{/if}>
                        </div>
                        x
                        <div class="form-group">
                            <input type="number" class="form-control" id="video-vim-height" name="video-vim-height" value="{$properties['video-vim-height']}" {if $properties['video-vim-responsive'] === '1'} disabled{/if}>
                        </div>
                        Pixel
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-vim-autoplay" name="video-vim-autoplay" value="1" {if $properties['video-vim-autoplay'] === '1'} checked="checked"{/if}> Video automatisch starten?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-vim-loop" name="video-vim-loop" value="0" {if $properties['video-vim-loop'] === '0'} checked="checked"{/if}> Video nach Ablauf wiederholen?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-vim-img" name="video-vim-img" value="1" {if $properties['video-vim-img'] === '1'} checked="checked"{/if}> Bild anzeigen?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-vim-title" name="video-vim-title" value="1" {if $properties['video-vim-title'] === '1'} checked="checked"{/if}> Titel anzeigen?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="video-vim-byline" name="video-vim-byline" value="1" {if $properties['video-vim-byline'] === '1'} checked="checked"{/if}> Verfasserangabe anzeigen?
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group vimeo-color-picker colorpicker-element">
                        <input type="text" class="form-control" name="video-vim-color" id="video-vim-color" value="{$properties['video-vim-color']}">
                        <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                    </div>
                    <span class="help-block">Farbe</span>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function(){
            $('input[name="video-vendor"]').click(function(){
                if ($(this).val() == 'youtube'){
                    $('#youtube-container').show();
                    $('#vimeo-container').hide();
                }else{
                    $('#youtube-container').hide();
                    $('#vimeo-container').show();
                }
            });

            $('input[name="video-yt-responsive"]').click(function(){
                if ($(this).prop('checked')){
                    $('input[name="video-yt-width"], input[name="video-yt-height"]').prop('disabled', true);
                } else {
                    $('input[name="video-yt-width"], input[name="video-yt-height"]').prop('disabled', false);
                }
            });

            $('input[name="video-vim-responsive"]').click(function(){
                if ($(this).prop('checked')){
                    $('input[name="video-vim-width"], input[name="video-vim-height"]').prop('disabled', true);
                } else {
                    $('input[name="video-vim-width"], input[name="video-vim-height"]').prop('disabled', false);
                }
            });

            $('input[name="video-yt-height"]').change(function(){
                var width = $(this).val();
                $('input[name="video-yt-width"]').val(parseInt((width/9)*16));
            });
            $('input[name="video-yt-width"]').change(function(){
                var width = $(this).val();
                $('input[name="video-yt-height"]').val(parseInt((width/16)*9));
            });

            $('input[name="video-vim-height"]').change(function(){
                var width = $(this).val();
                $('input[name="video-vim-width"]').val(parseInt((width/9)*16));
            });
            $('input[name="video-vim-width"]').change(function(){
                var width = $(this).val();
                $('input[name="video-vim-height"]').val(parseInt((width/16)*9));
            });

            $('#config-modal-body .vimeo-color-picker').colorpicker({
                format:'hex',
                colorSelectors: {
                    '#ffffff': '#ffffff',
                    '#777777': '#777777',
                    '#337ab7': '#337ab7',
                    '#5cb85c': '#5cb85c',
                    '#5bc0de': '#5bc0de',
                    '#f0ad4e': '#f0ad4e',
                    '#d9534f': '#d9534f',
                    '#000000': '#000000'
                }
            });
            $('#config-modal-body #video-vim-color').click(function(){
                $('#config-modal-body .vimeo-color-picker').colorpicker('show');
            });
        });
    </script>

    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>