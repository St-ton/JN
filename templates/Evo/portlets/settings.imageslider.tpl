<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation">
        <a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">
            General
        </a>
    </li>
    <li role="presentation">
        <a aria-controls="slides" data-toggle="tab" role="tab" id="slides-tab" href="#slides">
            Slides
        </a>
    </li>
    <li role="presentation">
        <a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">
            Style
        </a>
    </li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="tabpanel" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-theme">Theme</label>
                    <select class="form-control" id="slider-theme" name="slider-theme">
                        <option value="default"{if $properties['slider-theme'] === 'default'} selected{/if}>Default</option>
                        <option value="bar"{if $properties['slider-theme'] === 'bar'} selected{/if}>Bar</option>
                        <option value="light"{if $properties['slider-theme'] === 'light'} selected{/if}>Light</option>
                        <option value="dark"{if $properties['slider-theme'] === 'dark'} selected{/if}>Dark</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-animation-speed">Animation Geschwindigkeit (in ms)</label>
                    <input type="text" id="slider-animation-speed" name="slider-animation-speed" class="form-control" value="{$properties['slider-animation-speed']}">
                    <span class="help-block">Der Wert von "Animations Geschwindigkeit" darf den Wert von "Pause Zeit" nicht überschreiten!</span>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-animation-pause">Pause Zeit (in ms)</label>
                    <input type="text" id="slider-animation-pause" name="slider-animation-pause" class="form-control" value="{$properties['slider-animation-pause']}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-class">Class name</label>
                    <input type="text" id="slider-class" name="attr[class]" class="form-control" value="{$properties.attr['class']}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-start">zufälliger Start?</label>
                    <div class="radio" id="slider-start">
                        <label class="radio-inline">
                            <input type="radio" name="slider-start" id="slider-start-0" value="false"{if $properties['slider-start'] === 'false'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-start" id="slider-start-1" value="true"{if $properties['slider-start'] === 'true'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-pause">Pause bei Hover</label>
                    <div class="radio" id="slider-pause">
                        <label class="radio-inline">
                            <input type="radio" name="slider-pause" id="slider-pause-0" value="false"{if $properties['slider-pause'] === 'false'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-pause" id="slider-pause-1" value="true"{if $properties['slider-pause'] === 'true'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-navigation">Navigation</label>
                    <div class="radio" id="slider-navigation">
                        <label class="radio-inline">
                            <input type="radio" name="slider-navigation" id="slider-navigation-0" value="false"{if $properties['slider-navigation'] === 'false'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-navigation" id="slider-navigation-1" value="true"{if $properties['slider-navigation'] === 'true'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-direction-navigation">Navigation - Richtung</label>
                    <div class="radio" id="slider-direction-navigation">
                        <label class="radio-inline">
                            <input type="radio" name="slider-direction-navigation" id="slider-direction-navigation-0" value="false"{if $properties['slider-direction-navigation'] === 'false'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-direction-navigation" id="slider-direction-navigation-1" value="true"{if $properties['slider-direction-navigation'] === 'true'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-kenburns">Ken-Burns-Effekt</label>
                    <div class="radio" id="slider-kenburns">
                        <label class="radio-inline">
                            <input type="radio" name="slider-kenburns" id="slider-kenburns-0" value="false"{if $properties['slider-kenburns'] === 'false'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-kenburns" id="slider-kenburns-1" value="true"{if $properties['slider-kenburns'] === 'true'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                    <span class="help-block"><i class="fa fa-warning"></i> Wenn diese Option aktiviert ist, überschreibt sie andere <a href="#" data-toggle="tooltip" title="" data-original-title="Es werden überschrieben: zufälliger Start, Pause bei Hover, Navigation, Thumbnail Navigation, Navigation (Richtung), Effekte.">Einstellungen</a>.</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-effects-random">zufällige Effekte</label>
                    <div class="radio" id="slider-effects-random">
                        <label class="radio-inline">
                            <input type="radio" name="slider-effects-random" id="slider-effects-random-0" value="false"{if $properties['slider-effects-random'] === 'false'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-effects-random" id="slider-effects-random-1" value="true"{if $properties['slider-effects-random'] === 'true'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="well" id="slider-effects-container"{if $properties['slider-effects-random'] === 'true'} style="display:none;"{/if}>
            <div id="effects">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceDown" name="effects[sliceDown]"
                                       value="sliceDown" {if !empty($properties['effects']['sliceDown']) && $properties['effects']['sliceDown'] === 'sliceDown'} checked="checked"{/if}>
                                sliceDown
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceDownLeft" name="effects[sliceDownLeft]"
                                       value="sliceDownLeft" {if !empty($properties['effects']['sliceDownLeft']) && $properties['effects']['sliceDownLeft'] === 'sliceDownLeft'} checked="checked"{/if}>
                                sliceDownLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUp" name="effects[sliceUp]"
                                       value="sliceUp" {if !empty($properties['effects']['sliceUp']) && $properties['effects']['sliceUp'] === 'sliceUp'} checked="checked"{/if}>
                                sliceUp
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUpLeft" name="effects[sliceUpLeft]"
                                       value="sliceUpLeft" {if !empty($properties['effects']['sliceUpLeft']) && $properties['effects']['sliceUpLeft'] === 'sliceUpLeft'} checked="checked"{/if}>
                                sliceUpLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUpDown" name="effects[sliceUpDown]"
                                       value="sliceUpDown" {if !empty($properties['effects']['sliceUpDown']) && $properties['effects']['sliceUpDown'] === 'sliceUpDown'} checked="checked"{/if}>
                                sliceUpDown
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUpDownLeft"
                                       name="effects[sliceUpDownLeft]"
                                       value="sliceUpDownLeft" {if !empty($properties['effects']['sliceUpDownLeft']) && $properties['effects']['sliceUpDownLeft'] === 'sliceUpDownLeft'} checked="checked"{/if}>
                                sliceUpDownLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-fold" name="effects[fold]"
                                       value="fold" {if !empty($properties['effects']['fold']) && $properties['effects']['fold'] === 'fold'} checked="checked"{/if}>
                                fold
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-fade" name="effects[fade]"
                                       value="fade" {if !empty($properties['effects']['fade']) && $properties['effects']['fade'] === 'fade'} checked="checked"{/if}>
                                fade
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-slideInRight" name="effects[slideInRight]"
                                       value="slideInRight" {if !empty($properties['effects']['slideInRight']) && $properties['effects']['slideInRight'] === 'slideInRight'} checked="checked"{/if}>
                                slideInRight
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-slideInLeft" name="effects[slideInLeft]"
                                       value="slideInLeft" {if !empty($properties['effects']['slideInLeft']) && $properties['effects']['slideInLeft'] === 'slideInLeft'} checked="checked"{/if}>
                                slideInLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRandom" name="effects[boxRandom]"
                                       value="boxRandom" {if !empty($properties['effects']['boxRandom']) && $properties['effects']['boxRandom'] === 'boxRandom'} checked="checked"{/if}>
                                boxRandom
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRain" name="effects[boxRain]"
                                       value="boxRain" {if !empty($properties['effects']['boxRain']) && $properties['effects']['boxRain'] === 'boxRain'} checked="checked"{/if}>
                                boxRain
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRainReverse" name="effects[boxRainReverse]"
                                       value="boxRainReverse" {if !empty($properties['effects']['boxRainReverse']) && $properties['effects']['boxRainReverse'] === 'boxRainReverse'} checked="checked"{/if}>
                                boxRainReverse
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRainGrow" name="effects[boxRainGrow]"
                                       value="boxRainGrow" {if !empty($properties['effects']['boxRainGrow']) && $properties['effects']['boxRainGrow'] === 'boxRainGrow'} checked="checked"{/if}>
                                boxRainGrow
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRainGrowReverse"
                                       name="effects[boxRainGrowReverse]"
                                       value="boxRainGrowReverse" {if !empty($properties['effects']['boxRainGrowReverse']) && $properties['effects']['boxRainGrowReverse'] === 'boxRainGrowReverse'} checked="checked"{/if}>
                                boxRainGrowReverse
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function(){
                $('input[name="slider-effects-random"]').click(function(){
                    if ($(this).val() == 'false'){
                        $('#slider-effects-container').show();
                    }else{
                        $('#slider-effects-container').hide();
                    }
                });
            });
        </script>
    </div>
    <div id="slides" class="tab-pane fade" role="tabpanel" aria-labelledby="slides-tab">
        {function name=slide level=0}
            <tr class="text-vcenter" id="{$kSlide}">
                <td class="tcenter">
                    <input id="{$kSlide}-url" type="hidden" name="slides[{$kSlide}][url]"
                           value=""/>
                    <input type="hidden" class="form-control" id="slides[{$kSlide}][nSort]"
                           name="slides[{$kSlide}][nSort]" value="{if $kSlide}{$smarty.foreach.slide.iteration}{/if}"
                           autocomplete="off"/>
                    <i class="btn btn-primary fa fa-bars"></i>
                </td>
                <td class="tcenter"><img
                            src="templates/bootstrap/gfx/layout/upload.png"
                            id="{$kSlide}-img" onclick="jleHost.onOpenKCFinder(kcfinderCallback.bind(this, '{$kSlide}'));"
                            alt="Slidergrafik" class="img-responsive" role="button"/></td>
                <td class="tcenter">
                    <input class="form-control margin2" id="cTitle{$kSlide}" type="text"
                           name="slides[{$kSlide}][cTitle]" value=""
                           placeholder="Titel"/>
                    <input class="form-control margin2" id="cLink{$kSlide}" type="text" name="slides[{$kSlide}][cLink]"
                           value="" placeholder="Link"/>
                </td>
                <td><textarea class="form-control vheight" id="cText{$kSlide}" name="slides[{$kSlide}][cText]"
                              maxlength="255"
                              placeholder="Text"></textarea></td>
                <td class="vcenter">
                    <button type="button" onclick="$(this).parent().parent().remove();sortSlide();"
                            class="slide_delete btn btn-danger btn-block fa fa-trash" title="L&ouml;schen"></button>
                </td>
            </tr>
        {/function}
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">fügen Sie hier die einzelnen Bilder hinzu</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="tableSlide" class="table">
                            <thead>
                            <tr>
                                <th class="tleft"></th>
                                <th width="20%">Bild</th>
                                <th width="35%">Titel / Link</th>
                                <th width="35%">Text</th>
                                <th width="5%"></th>
                            </tr>
                            </thead>
                            <tbody>
                               {foreach from=$properties['slides'] item=slide}
                                   {if !empty($slide['url'])}
                                       <tr class="text-vcenter" id="slide{$slide.nSort}">
                                           <td class="tcenter">
                                               <input id="slide{$slide.nSort}-url" type="hidden" name="slides[slide{$slide.nSort}][url]"
                                                      value="{if isset($slide['url'])}{$slide['url']}{/if}"/>
                                               <input type="hidden" class="form-control" id="slides[slide{$slide.nSort}][nSort]"
                                                      name="slides[slide{$slide.nSort}][nSort]" value="{$slide.nSort}"
                                                      autocomplete="off"/>
                                               <i class="btn btn-primary fa fa-bars"></i>
                                           </td>
                                           <td class="tcenter"><img
                                                       src="{if isset($slide['url'])}{$slide['url']}{else}templates/bootstrap/gfx/layout/upload.png{/if}"
                                                       id="slide{$slide.nSort}-img" onclick="jleHost.onOpenKCFinder(kcfinderCallback.bind(this, '{$slide.nSort}'));"
                                                       alt="Slidergrafik" class="img-responsive" role="button"/></td>
                                           <td class="tcenter">
                                               <input class="form-control margin2" id="title{$slide.nSort}" type="text"
                                                      name="slides[slide{$slide.nSort}][cTitle]" value="{if isset($slide['cTitle'])}{$slide['cTitle']}{/if}"
                                                      placeholder="Title"/>
                                               <input class="form-control margin2" id="cLink{$slide.nSort}" type="text" name="slides[slide{$slide.nSort}][cLink]"
                                                      value="{if isset($slide['cLink'])}{$slide['cLink']}{/if}" placeholder="Link"/>
                                           </td>
                                           <td><textarea class="form-control vheight" id="cText{$slide.nSort}" name="slides[slide{$slide.nSort}][cText]"
                                                         maxlength="255"
                                                         placeholder="Text">{if isset($slide.cText)}{$slide.cText}{/if}</textarea></td>
                                           <td class="vcenter">
                                               <button type="button" onclick="$(this).parent().parent().remove();sortSlide();"
                                                       class="slide_delete btn btn-danger btn-block fa fa-trash" title="L&ouml;schen"></button>
                                           </td>
                                       </tr>
                                   {/if}
                               {/foreach}
                            </tbody>
                        </table>
                    </div>
                    <table class="hidden"><tbody id="newSlide">{slide oSlide=null kSlide='NEU'}</tbody></table>
                    <div class="panel-footer">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" onclick="addSlide();"><i class="glyphicon glyphicon-plus"></i> Hinzuf&uuml;gen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function kcfinderCallback(id, url) {
                $('#'+id+'-url').val(url);
                $('#'+id+'-img').attr('src', url);
            }

            var count = {if isset($properties.slides)}{$properties.slides|@count}{else}0{/if};
            function addSlide(slide) {
                var new_slide = $('#newSlide').html();
                new_slide = new_slide.replace(/NEU/g, "slide"+count);
                $('#tableSlide tbody').append( new_slide );
                count++;
                sortSlide();
            }

            function sortSlide() {
                $("input[name*='\[nSort\]']").each(function(index) {
                    $(this).val(index+1);
                });
            }
            $(function(){
                $("#tableSlide tbody ").sortable({
                    containerSelector: 'table',
                    itemPath: '> tbody',
                    itemSelector: 'tr',
                    opacity : '0',
                    axis : "y",
                    cursor: "move",
                    stop : function(item) {
                        sortSlide();
                    }
                });
            });
        </script>
    </div>
    {include file='./settings.tabcontent.style.tpl'}
</div>
