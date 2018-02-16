<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation">
        <a aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">
            General
        </a>
    </li>
    <li role="presentation">
        <a aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">
            Animation
        </a>
    </li>
    <li role="presentation" class="">
        <a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design">
            Style
        </a>
    </li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <input type="hidden" name="widthHeuristics[lg]" value="1">
        <input type="hidden" name="widthHeuristics[md]" value="1">
        <input type="hidden" name="widthHeuristics[sm]" value="1">
        <input type="hidden" name="widthHeuristics[xs]" value="1">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="parallax-flag">Parallax-Effekt nutzen?</label>
                    <div class="radio" id="parallax-flag">
                        <label class="radio-inline">
                            <input type="radio" name="parallax-flag" value="no"{if $properties['parallax-flag'] === 'no'} checked="checked"{/if}> Nein
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="parallax-flag" value="yes"{if $properties['parallax-flag'] === 'yes'} checked="checked"{/if}> Ja
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="parallax-id">ID</label>
                    <input type="text" id="parallax-id" name="attr[id]" class="form-control" value="{$properties.attr['id']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="parallax-class">Class name</label>
                    <input type="text" id="parallax-class" name="attr[class]" class="form-control" value="{$properties.attr['class']}">
                </div>
            </div>
        </div>
        <div id="parallax-setting-container" {if $properties['parallax-flag'] === 'no'}style="display:none;"{/if}>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="parallax-min-height">Mindesthöhe des Containers</label>
                        <input type="number" class="form-control" id="parallax-min-height" name="style[min-height]" value="{$properties.style['min-height']}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="parallax-btn-img">Bild</label>
                        <input type="hidden" id="parallax-url" name="src" value="{if empty($properties['src'])}../gfx/keinBild.gif{else}{$properties['src']}{/if}">
                        <button type="button" class="btn btn-default cle-image-btn" onclick="editor.onOpenKCFinder(kcfinderCallback);">
                            {if isset($properties['src'])}
                                <img src="{if empty($properties['src'])}../gfx/keinBild.gif{else}{$properties['src']}{/if}" id="parallax-btn-img" alt="einzufügendes Bild">
                            {else}
                                Bild auswählen
                            {/if}
                        </button>
                    </div>

                </div>
            </div>
        </div>
        <script>
            $(function(){
                $('input[name="parallax-flag"]').click(function(){
                    if ($(this).val() == 'yes'){
                        $('#parallax-setting-container').show();
                    }else{
                        $('#parallax-setting-container').hide();
                    }
                });
            });

            function kcfinderCallback(url) {
                $('#parallax-url').val(url);
                $('#parallax-btn-img').attr('src', url);
            }
        </script>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>
