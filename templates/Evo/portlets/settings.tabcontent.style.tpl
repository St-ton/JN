<div id="style-design" class="tab-pane fade in" role="tabpanel" aria-labelledby="style-design-tab">
    <div class="row">
        <label for="colors" class="col-sm-2 form-control-static">Colors</label>
        <div class="col-sm-10" id="colors">
            <div class="row">
                <div class="col-sm-6">
                    <div class="input-group background-color-picker colorpicker-element">
                        <input type="text" class="form-control" name="style[background-color]" id="background-color" value="{$properties['style']['background-color']}">
                        <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                    </div>
                    <span class="help-block">Background-color</span>
                </div>
                <div class="col-sm-6">
                    <div class="input-group font-color-picker colorpicker-element">
                        <input type="text" class="form-control" name="style[color]" id="font-color" value="{$properties['style']['color']}">
                        <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
                    </div>
                    <span class="help-block">Font-color</span>
                </div>
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
                        <input type="text" class="form-control" name="style[margin-top]" value="{$properties['style']['margin-top']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Top</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[margin-right]" value="{$properties['style']['margin-right']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Right</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[margin-bottom]" value="{$properties['style']['margin-bottom']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Bottom</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[margin-left]" value="{$properties['style']['margin-left']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Left</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row ">
        <label for="padding" class="col-sm-2 form-control-static">Padding</label>
        <div class="col-sm-10" id="padding">
            <div class="row">
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[padding-top]" id="padding-top" value="{$properties['style']['padding-top']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Top</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[padding-right]" id="padding-right" value="{$properties['style']['padding-right']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Right</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[padding-bottom]" id="padding-bottom" value="{$properties['style']['padding-bottom']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Bottom</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[padding-left]" id="padding-left" value="{$properties['style']['padding-left']}">
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
                        <input type="text" class="form-control" name="style[border-top-width]" id="border-top-width" value="{$properties['style']['border-top-width']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Top</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[border-right-width]" id="border-right-width" value="{$properties['style']['border-right-width']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Right</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[border-bottom-width]" id="border-bottom-width" value="{$properties['style']['border-bottom-width']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Bottom</span>
                </div>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="style[border-left-width]" id="border-left-width" value="{$properties['style']['border-left-width']}">
                        <span class="input-group-addon">px</span>
                    </div>
                    <span class="help-block">Left</span>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-sm-5 col-sm-offset-2">
            <select  class="form-control" name="style[border-style]" id="border-style">
                <option value=""></option>
                <option value="none"{if $properties['style']['border-style'] === 'none'} selected{/if}>none</option>
                <option value="hidden"{if $properties['style']['border-style'] === 'hidden'} selected{/if}>hidden</option>
                <option value="dotted"{if $properties['style']['border-style'] === 'dotted'} selected{/if}>dotted</option>
                <option value="dashed"{if $properties['style']['border-style'] === 'dashed'} selected{/if}>dashed</option>
                <option value="solid"{if $properties['style']['border-style'] === 'solid'} selected{/if}>solid</option>
                <option value="double"{if $properties['style']['border-style'] === 'double'} selected{/if}>double</option>
                <option value="groove"{if $properties['style']['border-style'] === 'groove'} selected{/if}>groove</option>
                <option value="ridge"{if $properties['style']['border-style'] === 'ridge'} selected{/if}>ridge</option>
                <option value="inset"{if $properties['style']['border-style'] === 'inset'} selected{/if}>inset</option>
                <option value="outset"{if $properties['style']['border-style'] === 'outset'} selected{/if}>outset</option>
                <option value="initial"{if $properties['style']['border-style'] === 'initial'} selected{/if}>initial</option>
                <option value="inherit"{if $properties['style']['border-style'] === 'inherit'} selected{/if}>inherit</option>
            </select> <span class="help-block">Style</span>
        </div>
        <div class="col-sm-5">
            <div class="input-group border-color-picker colorpicker-element">
                <input type="text" class="form-control" name="style[border-color]" id="border-color" value="{$properties['style']['border-color']}">
                <span class="input-group-addon"><i style="margin-right: 0px;"></i></span>
            </div>
            <span class="help-block">Color</span>
        </div>
    </div>
    <script>
        $(function(){
            $('#config-modal-body .background-color-picker').colorpicker({
                format:'rgba',
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
                format:'rgba',
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

            $('#config-modal-body .font-color-picker').colorpicker({
                format:'rgba',
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
            $('#config-modal-body #font-color').click(function(){
                $('#config-modal-body .font-color-picker').colorpicker('show');
            });

            $('input[name="link-flag"]').click(function(){
                if ($(this).val() == 'yes'){
                    $('#url-link-container').show();
                }else{
                    $('#url-link-container').hide();
                }
            });
        });
    </script>
</div>
