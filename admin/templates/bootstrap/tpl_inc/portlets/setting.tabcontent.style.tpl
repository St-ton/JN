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
        <div class="col-sm-10" id="padding">
            <div class="row">
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
    </script>
</div>
