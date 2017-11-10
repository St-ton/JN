<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-controls="slides" data-toggle="tab" role="tab" id="slides-tab" href="#slides">Slides</a></li>
    <li role="presentation"><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
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
                    <input type="text" id="slider-class" name="slider-class" class="form-control" value="{$properties['slider-class']}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-start">zufälliger Start?</label>
                    <div class="radio" id="slider-start">
                        <label class="radio-inline">
                            <input type="radio" name="slider-start" id="slider-start-0" value="no"{if $properties['slider-start'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-start" id="slider-start-1" value="yes"{if $properties['slider-start'] === 'yes'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-pause">Pause bei Hover</label>
                    <div class="radio" id="slider-pause">
                        <label class="radio-inline">
                            <input type="radio" name="slider-pause" id="slider-pause-0" value="no"{if $properties['slider-pause'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-pause" id="slider-pause-1" value="yes"{if $properties['slider-pause'] === 'yes'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-navigation">Navigation</label>
                    <div class="radio" id="slider-navigation">
                        <label class="radio-inline">
                            <input type="radio" name="slider-navigation" id="slider-navigation-0" value="no"{if $properties['slider-navigation'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-navigation" id="slider-navigation-1" value="yes"{if $properties['slider-navigation'] === 'yes'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-thumb-navigation">Navigation - Thumbnail</label>
                    <div class="radio" id="slider-thumb-navigation">
                        <label class="radio-inline">
                            <input type="radio" name="slider-thumb-navigation" id="slider-thumb-navigation-0" value="no"{if $properties['slider-thumb-navigation'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-thumb-navigation" id="slider-thumb-navigation-1" value="yes"{if $properties['slider-thumb-navigation'] === 'yes'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-direction-navigation">Navigation - Richtung</label>
                    <div class="radio" id="slider-direction-navigation">
                        <label class="radio-inline">
                            <input type="radio" name="slider-direction-navigation" id="slider-direction-navigation-0" value="no"{if $properties['slider-direction-navigation'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-direction-navigation" id="slider-direction-navigation-1" value="yes"{if $properties['slider-direction-navigation'] === 'yes'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="slider-kenburns">Ken-Burns-Effekt</label>
                    <div class="radio" id="slider-kenburns">
                        <label class="radio-inline">
                            <input type="radio" name="slider-kenburns" id="slider-kenburns-0" value="no"{if $properties['slider-kenburns'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-kenburns" id="slider-kenburns-1" value="yes"{if $properties['slider-kenburns'] === 'yes'} checked="checked"{/if}> Yes
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
                            <input type="radio" name="slider-effects-random" id="slider-effects-random-0" value="no"{if $properties['slider-effects-random'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="slider-effects-random" id="slider-effects-random-1" value="yes"{if $properties['slider-effects-random'] === 'yes'} checked="checked"{/if}> Yes
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="well" id="slider-effects-container"{if $properties['slider-effects-random'] === 'yes'} style="display:none;"{/if}>
            <div id="effects">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceDown" name="slider-effects-sliceDown" value="yes" {if $properties['slider-effects-sliceDown'] === 'yes'} checked="checked"{/if}> sliceDown
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceDownLeft" name="slider-effects-sliceDownLeft" value="yes" {if $properties['slider-effects-sliceDownLeft'] === 'yes'} checked="checked"{/if}> sliceDownLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUp" name="slider-effects-sliceUp" value="yes" {if $properties['slider-effects-sliceUp'] === 'yes'} checked="checked"{/if}> sliceUp
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUpLeft" name="slider-effects-sliceUpLeft" value="yes" {if $properties['slider-effects-sliceUpLeft'] === 'yes'} checked="checked"{/if}> sliceUpLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUpDown" name="slider-effects-sliceUpDown" value="yes" {if $properties['slider-effects-sliceUpDown'] === 'yes'} checked="checked"{/if}> sliceUpDown
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-sliceUpDownLeft" name="slider-effects-sliceUpDownLeft" value="yes" {if $properties['slider-effects-sliceUpDownLeft'] === 'yes'} checked="checked"{/if}> sliceUpDownLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-fold" name="slider-effects-fold" value="yes" {if $properties['slider-effects-fold'] === 'yes'} checked="checked"{/if}> fold
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-fade" name="slider-effects-fade" value="yes" {if $properties['slider-effects-fade'] === 'yes'} checked="checked"{/if}> fade
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-slideInRight" name="slider-effects-slideInRight" value="yes" {if $properties['slider-effects-slideInRight'] === 'yes'} checked="checked"{/if}> slideInRight
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-slideInLeft" name="slider-effects-slideInLeft" value="yes" {if $properties['slider-effects-slideInLeft'] === 'yes'} checked="checked"{/if}> slideInLeft
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRandom" name="slider-effects-boxRandom" value="yes" {if $properties['slider-effects-boxRandom'] === 'yes'} checked="checked"{/if}> boxRandom
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRain" name="slider-effects-boxRain" value="yes" {if $properties['slider-effects-boxRain'] === 'yes'} checked="checked"{/if}> boxRain
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRainReverse" name="slider-effects-boxRainReverse" value="yes" {if $properties['slider-effects-boxRainReverse'] === 'yes'} checked="checked"{/if}> boxRainReverse
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRainGrow" name="slider-effects-boxRainGrow" value="yes" {if $properties['slider-effects-boxRainGrow'] === 'yes'} checked="checked"{/if}> boxRainGrow
                            </label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="slider-effects-boxRainGrowReverse" name="slider-effects-boxRainGrowReverse" value="yes" {if $properties['slider-effects-boxRainGrowReverse'] === 'yes'} checked="checked"{/if}> boxRainGrowReverse
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function(){
                $('input[name="slider-effects-random"]').click(function(){
                    if ($(this).val() == 'no'){
                        $('#slider-effects-container').show();
                    }else{
                        $('#slider-effects-container').hide();
                    }
                });
            });
        </script>
    </div>
    <div id="slides" class="tab-pane fade" role="slides" aria-labelledby="slides-tab">
        {function name=slide level=0}
            <tr class="text-vcenter" id="{$kSlide}">
                <td class="tcenter">
                    <input id="{$kSlide}-url" type="hidden" name="aSlide[{$kSlide}][url]"
                           value="{if isset($oSlide->cBild)}{$oSlide->cBild}{/if}"/>
                    <input type="hidden" class="form-control" id="aSlide[{$kSlide}][nSort]"
                           name="aSlide[{$kSlide}][nSort]" value="{if $kSlide}{$smarty.foreach.slide.iteration}{/if}"
                           autocomplete="off"/>
                    <i class="btn btn-primary fa fa-bars"></i>
                </td>
                <td class="tcenter"><img
                            src="{if isset($oSlide->cBildAbsolut)}{$oSlide->cBildAbsolut}{else}templates/bootstrap/gfx/layout/upload.png{/if}"
                            id="{$kSlide}-img" onclick="jleHost.onOpenKCFinder(kcfinderCallback.bind(this, '{$kSlide}'));"
                            alt="Slidergrafik" class="img-responsive" role="button"/></td>
                <td class="tcenter">
                    <input class="form-control margin2" id="cTitel{$kSlide}" type="text"
                           name="aSlide[{$kSlide}][cTitel]" value="{if isset($oSlide->cTitel)}{$oSlide->cTitel}{/if}"
                           placeholder="Titel"/>
                    <input class="form-control margin2" id="cLink{$kSlide}" type="text" name="aSlide[{$kSlide}][cLink]"
                           value="{if isset($oSlide->cLink)}{$oSlide->cLink}{/if}" placeholder="Link"/>
                </td>
                <td><textarea class="form-control vheight" id="cText{$kSlide}" name="aSlide[{$kSlide}][cText]"
                              maxlength="255"
                              placeholder="Text">{if isset($oSlide->cText)}{$oSlide->cText}{/if}</textarea></td>
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
                                <tr class="text-vcenter" id="slide0">
                                    <td class="tcenter">
                                        <input id ="slide0-url" type="hidden" name="aSlide[0][url]" value="{if isset($oSlide->cBild)}{$oSlide->cBild}{/if}" />
                                        <input type="hidden" class="form-control" id="aSlide[0][nSort]" name="aSlide[0][nSort]" value="0" autocomplete="off" />
                                        <i class="btn btn-primary fa fa-bars"></i>
                                    </td>
                                    <td class="tcenter">
                                        <img src="{if isset($oSlide->cBildAbsolut)}{$oSlide->cBildAbsolut}{else}templates/bootstrap/gfx/layout/upload.png{/if}"
                                             id="slide0-img"
                                             onclick="jleHost.onOpenKCFinder(kcfinderCallback.bind(this, 'slide0'));"
                                             alt="Slidergrafik" class="img-responsive" role="button"/>
                                    </td>
                                    <td class="tcenter">
                                        <input class="form-control margin2" id="cTitel0" type="text"
                                               name="aSlide[0][cTitel]"
                                               value="{if isset($oSlide->cTitel)}{$oSlide->cTitel}{/if}"
                                               placeholder="Titel"/>
                                        <input class="form-control margin2" id="cLink0" type="text"
                                               name="aSlide[0][cLink]"
                                               value="{if isset($oSlide->cLink)}{$oSlide->cLink}{/if}"
                                               placeholder="Link"/>
                                    </td>
                                    <td><textarea class="form-control vheight" id="cText0" name="aSlide[0][cText]"
                                                  maxlength="255"
                                                  placeholder="Text">{if isset($oSlide->cText)}{$oSlide->cText}{/if}</textarea>
                                    </td>
                                    <td class="vcenter">
                                        <button type="button"
                                                onclick="$(this).parent().parent().remove();sortSlide();"
                                                class="slide_delete btn btn-danger btn-block fa fa-trash"
                                                title="L&ouml;schen"></button>
                                    </td>
                                </tr>
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
                console.log(id, url);
                $('#'+id+'-url').val(url);
                $('#'+id+'-img').attr('src', url);
            }

            var count = 0;
            function addSlide(slide) {
                var new_slide = $('#newSlide').html();
                new_slide = new_slide.replace(/NEU/g, "neu"+count);
                $('#tableSlide tbody').append( new_slide );
                count++;
                sortSlide();
            }

            function sortSlide() {
                console.log('sort');
                $("input[name*='\[nSort\]']").each(function(index) {
                    console.log(index);
                    $(this).val(index+1);
                });
            }

        </script>
    </div>
    {include file='./settings.tabcontent.style.tpl'}
</div>
