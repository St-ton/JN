<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation" class=""><a aria-controls="url-link" data-toggle="tab" id="url-link-tab" role="tab" href="#url-link" aria-expanded="false">Url (link)</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="form-group">
            <label for="image-btn-img">Bild</label>
            <input type="hidden" id="img-url" name="url" value="{$properties.url}">
            <button type="button" class="btn btn-default jle-image-btn" onclick="jleHost.onOpenKCFinder(kcfinderCallback);">
                {if isset($properties.url)}
                    <img src="{$properties.url}" id="image-btn-img" alt="einzufügendes Bild">
                {else}
                    Bild auswählen
                {/if}
            </button>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-image-alt">Alternativtext</label>
                    <input name="alt" value="{$properties.alt}" class="form-control" id="config-img-alt">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-image-title">Bildtitel</label>
                    <input name="title" value="{$properties.title}" class="form-control" id="config-img-title">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-image-shape">Bildform</label>
                    <select name="shape" class="form-control" id="config-image-shape">
                        <option value=""{if $properties.shape === ''} selected{/if}>flat</option>
                        <option value="img-rounded"{if $properties.shape === 'img-rounded'} selected{/if}>abgerundete Ecken</option>
                        <option value="img-circle">{if $properties.shape === 'img-circle'} selected{/if}rund</option>
                        <option value="img-thumbnail"{if $properties.shape === 'img-thumbnail'} selected{/if}>Thumbnail</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="class">Class name</label>
                    <input type="text"  id="class" name="class" class="form-control" value="{$properties.class}">
                </div>
            </div>
        </div>
    </div>
    <div id="url-link" class="tab-pane fade in" role="tabpanel" aria-labelledby="url-link-tab">
        <div class="form-group">
            <label for="link-flag">URL (link)</label>
            <div class="radio" id="link-flag">
                <label class="radio-inline">
                    <input type="radio" name="link-flag" id="link-flag-0" value="no"{if $properties['link-flag'] === 'no'} checked="checked"{/if}> No
                </label>
                <label class="radio-inline">
                    <input type="radio" name="link-flag" id="link-flag-1" value="yes"{if $properties['link-flag'] === 'yes'} checked="checked"{/if}> Yes
                </label>
            </div>
        </div>
        <div id="url-link-container" class="well" {if $properties['link-flag'] === 'no'}style="display:none;"{/if}>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="link-url">Choose a link</label>
                        <input type="text" class="form-control" id="link-url" name="link-url" placeholder="URL: http://www.example.com" value="{$properties['link-url']}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="link-title">Link Title</label>
                        <input type="text" class="form-control" id="link-title" name="link-title" value="{$properties['link-title']}">
                    </div>
                </div>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="link-new-tab-flag" name="link-new-tab-flag" value="yes" {if $properties['link-new-tab-flag'] === 'yes'} checked="checked"{/if}> Open link in a new tab
                </label>
            </div>
        </div>
    </div>
    <div id="wow-animation" class="tab-pane fade in" role="tabpanel" aria-labelledby="wow-animation-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-style">Animation style</label>
                    <select id="animation-style" name="animation-style" class="form-control">
                        <option value="">None</option>
                        <optgroup label="Attention Seekers">
                            <option value="bounce"{if $properties['animation-style'] === 'bounce'} selected{/if}>bounce</option>
                            <option value="flash"{if $properties['animation-style'] === 'flash'} selected{/if}>flash</option>
                            <option value="pulse"{if $properties['animation-style'] === 'pulse'} selected{/if}>pulse</option>
                            <option value="rubberBand"{if $properties['animation-style'] === 'rubberBand'} selected{/if}>rubberBand</option>
                            <option value="shake"{if $properties['animation-style'] === 'shake'} selected{/if}>shake</option>
                            <option value="swing"{if $properties['animation-style'] === 'swing'} selected{/if}>swing</option>
                            <option value="tada"{if $properties['animation-style'] === 'tada'} selected{/if}>tada</option>
                            <option value="wobble"{if $properties['animation-style'] === 'wobble'} selected{/if}>wobble</option>
                            <option value="jello"{if $properties['animation-style'] === 'jello'} selected{/if}>jello</option>
                        </optgroup>

                        <optgroup label="Bouncing Entrances">
                            <option value="bounceIn"{if $properties['animation-style'] === 'bounceIn'} selected{/if}>bounceIn</option>
                            <option value="bounceInDown"{if $properties['animation-style'] === 'bounceInDown'} selected{/if}>bounceInDown</option>
                            <option value="bounceInLeft"{if $properties['animation-style'] === 'bounceInLeft'} selected{/if}>bounceInLeft</option>
                            <option value="bounceInRight"{if $properties['animation-style'] === 'bounceInRight'} selected{/if}>bounceInRight</option>
                            <option value="bounceInUp"{if $properties['animation-style'] === 'bounceInUp'} selected{/if}>bounceInUp</option>
                        </optgroup>

                        <optgroup label="Fading Entrances">
                            <option value="fadeIn"{if $properties['animation-style'] === 'fadeIn'} selected{/if}>fadeIn</option>
                            <option value="fadeInDown"{if $properties['animation-style'] === 'fadeInDown'} selected{/if}>fadeInDown</option>
                            <option value="fadeInDownBig"{if $properties['animation-style'] === 'fadeInDownBig'} selected{/if}>fadeInDownBig</option>
                            <option value="fadeInLeft"{if $properties['animation-style'] === 'fadeInLeft'} selected{/if}>fadeInLeft</option>
                            <option value="fadeInLeftBig"{if $properties['animation-style'] === 'fadeInLeftBig'} selected{/if}>fadeInLeftBig</option>
                            <option value="fadeInRight"{if $properties['animation-style'] === 'fadeInRight'} selected{/if}>fadeInRight</option>
                            <option value="fadeInRightBig"{if $properties['animation-style'] === 'fadeInRightBig'} selected{/if}>fadeInRightBig</option>
                            <option value="fadeInUp"{if $properties['animation-style'] === 'fadeInUp'} selected{/if}>fadeInUp</option>
                            <option value="fadeInUpBig"{if $properties['animation-style'] === 'fadeInUpBig'} selected{/if}>fadeInUpBig</option>
                        </optgroup>

                        <optgroup label="Flippers">
                            <option value="flip"{if $properties['animation-style'] === 'flip'} selected{/if}>flip</option>
                            <option value="flipInX"{if $properties['animation-style'] === 'flipInX'} selected{/if}>flipInX</option>
                            <option value="flipInY"{if $properties['animation-style'] === 'flipInY'} selected{/if}>flipInY</option>
                        </optgroup>

                        <optgroup label="Lightspeed">
                            <option value="lightSpeedIn"{if $properties['animation-style'] === 'lightSpeedIn'} selected{/if}>lightSpeedIn</option>
                        </optgroup>

                        <optgroup label="Rotating Entrances">
                            <option value="rotateIn"{if $properties['animation-style'] === 'rotateIn'} selected{/if}>rotateIn</option>
                            <option value="rotateInDownLeft"{if $properties['animation-style'] === 'rotateInDownLeft'} selected{/if}>rotateInDownLeft</option>
                            <option value="rotateInDownRight"{if $properties['animation-style'] === 'rotateInDownRight'} selected{/if}>rotateInDownRight</option>
                            <option value="rotateInUpLeft"{if $properties['animation-style'] === 'rotateInUpLeft'} selected{/if}>rotateInUpLeft</option>
                            <option value="rotateInUpRight"{if $properties['animation-style'] === 'rotateInUpRight'} selected{/if}>rotateInUpRight</option>
                        </optgroup>

                        <optgroup label="Sliding Entrances">
                            <option value="slideInUp"{if $properties['animation-style'] === 'slideInUp'} selected{/if}>slideInUp</option>
                            <option value="slideInDown"{if $properties['animation-style'] === 'slideInDown'} selected{/if}>slideInDown</option>
                            <option value="slideInLeft"{if $properties['animation-style'] === 'slideInLeft'} selected{/if}>slideInLeft</option>
                            <option value="slideInRight"{if $properties['animation-style'] === 'slideInRight'} selected{/if}>slideInRight</option>

                        </optgroup>

                        <optgroup label="Zoom Entrances">
                            <option value="zoomIn"{if $properties['animation-style'] === 'zoomIn'} selected{/if}>zoomIn</option>
                            <option value="zoomInDown"{if $properties['animation-style'] === 'zoomInDown'} selected{/if}>zoomInDown</option>
                            <option value="zoomInLeft"{if $properties['animation-style'] === 'zoomInLeft'} selected{/if}>zoomInLeft</option>
                            <option value="zoomInRight"{if $properties['animation-style'] === 'zoomInRight'} selected{/if}>zoomInRight</option>
                            <option value="zoomInUp"{if $properties['animation-style'] === 'zoomInUp'} selected{/if}>zoomInUp</option>
                        </optgroup>

                        <optgroup label="Specials">
                            <option value="hinge"{if $properties['animation-style'] === 'hinge'} selected{/if}>hinge</option>
                            <option value="rollIn"{if $properties['animation-style'] === 'rollIn'} selected{/if}>rollIn</option>
                        </optgroup>

                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-duration">Duration</label>
                    <input type="text" class="form-control" id="animation-duration" name="animation-duration" value="{$properties['animation-duration']}">
                    <span class="help-block">Change the animation duration.</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-delay">Delay</label>
                    <input type="text" class="form-control" id="animation-delay" name="animation-delay" value="{$properties['animation-delay']}">
                    <span class="help-block">Delay before the animation starts.</span>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-offset">Offset</label>
                    <input type="text" class="form-control" id="animation-offset" name="animation-offset" value="{$properties['animation-offset']}">
                    <span class="help-block">Distance to start the animation.</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="animation-iteration">Iteration</label>
                    <input type="text" class="form-control" id="animation-iteration" name="animation-iteration" value="{$properties['animation-iteration']}">
                    <span class="help-block">The animation number times is repeated.</span>
                </div>
            </div>
        </div>
    </div>
    <div id="style-design" class="tab-pane fade in" role="tabpanel" aria-labelledby="style-design-tab">
        <div class="row">
            <label for="background-color" class="col-sm-4 form-control-static">Background color</label>
            <div class="col-sm-4">
                <div class="input-group background-color-picker colorpicker-element">
                    <input type="text" class="form-control" name="background-color" id="background-color" value="{$properties['background-color']}">
                    <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <label for="margin" class="col-sm-2 form-control-static">Margin</label>
            <div class="col-sm-10" id="margin">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-top" value="{$properties['margin-top']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Top</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-right" value="{$properties['margin-right']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Right</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-bottom" value="{$properties['margin-bottom']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Bottom</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="margin-left" value="{$properties['margin-left']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Left</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <label for="padding" class="col-sm-2 form-control-static">Padding</label>
            <div class="col-sm-10">
                <div class="row" id="padding">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-top" id="padding-top" value="{$properties['padding-top']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Top</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-right" id="padding-right" value="{$properties['padding-right']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Right</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-bottom" id="padding-bottom" value="{$properties['padding-bottom']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Bottom</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="padding-left" id="padding-left" value="{$properties['padding-left']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Left</span>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row ">
            <label for="border-width" class="col-sm-2 form-control-static">Border</label>
            <div class="col-sm-10" id="border-width">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-top-width" id="border-top-width" value="{$properties['border-top-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Top</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-right-width" id="border-right-width" value="{$properties['border-right-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Right</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-bottom-width" id="border-bottom-width" value="{$properties['border-bottom-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Bottom</span>
                    </div>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="border-left-width" id="border-left-width" value="{$properties['border-left-width']}">
                            <span class="input-group-addon">px</span>
                        </div>
                        <span class="help-block">Left</span>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-sm-4 col-sm-offset-2">
                <select  class="form-control" name="border-style" id="border-style">
                    <option value=""></option>
                    <option value="none"{if $properties['border-style'] === 'none'} selected{/if}>none</option>
                    <option value="hidden"{if $properties['border-style'] === 'hidden'} selected{/if}>hidden</option>
                    <option value="dotted"{if $properties['border-style'] === 'dotted'} selected{/if}>dotted</option>
                    <option value="dashed"{if $properties['border-style'] === 'dashed'} selected{/if}>dashed</option>
                    <option value="solid"{if $properties['border-style'] === 'solid'} selected{/if}>solid</option>
                    <option value="double"{if $properties['border-style'] === 'double'} selected{/if}>double</option>
                    <option value="groove"{if $properties['border-style'] === 'groove'} selected{/if}>groove</option>
                    <option value="ridge"{if $properties['border-style'] === 'ridge'} selected{/if}>ridge</option>
                    <option value="inset"{if $properties['border-style'] === 'inset'} selected{/if}>inset</option>
                    <option value="outset"{if $properties['border-style'] === 'outset'} selected{/if}>outset</option>
                    <option value="initial"{if $properties['border-style'] === 'initial'} selected{/if}>initial</option>
                    <option value="inherit"{if $properties['border-style'] === 'inherit'} selected{/if}>inherit</option>
                </select> <span class="help-block">Style</span>
            </div>
            <div class="col-sm-4">
                <div class="input-group border-color-picker colorpicker-element">
                    <input type="text" class="form-control" name="border-color" id="border-color" value="{$properties['border-color']}">
                    <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                </div>
                <span class="help-block">Color</span>
            </div>
        </div>
    </div>
    <script>
        $(function(){
            $('#config-modal-body .background-color-picker').colorpicker({
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
            $('#config-modal-body #background-color').click(function(){
                $('#config-modal-body .background-color-picker').colorpicker('show');
            });

            $('#config-modal-body .border-color-picker').colorpicker({
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
            $('#config-modal-body #border-color').click(function(){
                $('#config-modal-body .border-color-picker').colorpicker('show');
            });

            $('input[name="link-flag"]').click(function(){
                if ($(this).val() == 'yes'){
                    $('#url-link-container').show();
                }else{
                    $('#url-link-container').hide();
                }
            });
        });

        function kcfinderCallback(url) {
            $('#img-url').val(url);
            $('#image-btn-img').attr('src', url);
        }
    </script>
</div>
