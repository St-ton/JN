<div class="box-styles row">
    <div class="box-config col-7">
        <div class="outer-box">
            <div class="top-row">
                <div>
                    {__('margin')} (px)
                </div>
                <label class="mid-top-col">
                    <input id="margin-top-input" class="form-control" tabindex="1"
                           name="{$propname}[margin-top]" value="{$propval['margin-top']}">
                </label>
                <div class="one-third"></div>
            </div>
            <div class="mid-row">
                <label>
                    <input id="margin-left-input" class="form-control" tabindex="4"
                           name="{$propname}[margin-left]" value="{$propval['margin-left']}">
                </label>
                <div class="border-box">
                    <div class="top-row">
                        <div>
                            {__('border')} (px)
                        </div>
                        <label class="mid-top-col">
                            <input id="border-top-input" class="form-control" tabindex="5"
                                   name="{$propname}[border-top-width]" value="{$propval['border-top-width']}">
                        </label>
                        <div class="one-third"></div>
                    </div>
                    <div class="mid-row">
                        <label>
                            <input id="border-left-input" class="form-control" tabindex="8"
                                   name="{$propname}[border-left-width]" value="{$propval['border-left-width']}">
                        </label>
                        <div class="padding-box">
                            <div class="top-row">
                                <div>
                                    {__('padding')} (px)
                                </div>
                                <label class="mid-top-col">
                                    <input id="padding-top-input" class="form-control" tabindex="9"
                                           name="{$propname}[padding-top]" value="{$propval['padding-top']}">
                                </label>
                                <div class="one-third"></div>
                            </div>
                            <div class="mid-row">
                                <label>
                                    <input id="padding-left-input" class="form-control" tabindex="12"
                                           name="{$propname}[padding-left]" value="{$propval['padding-left']}">
                                </label>
                                <div class="content-box"></div>
                                <label>
                                    <input id="padding-right-input" class="form-control" tabindex="10"
                                           name="{$propname}[padding-right]" value="{$propval['padding-right']}">
                                </label>
                            </div>
                            <label class="bottom-row">
                                <input id="padding-bottom-input" class="form-control" tabindex="11"
                                       name="{$propname}[padding-bottom]" value="{$propval['padding-bottom']}">
                            </label>
                        </div>
                        <label>
                            <input id="border-right-input" class="form-control" tabindex="6"
                                   name="{$propname}[border-right-width]" value="{$propval['border-right-width']}">
                        </label>
                    </div>
                    <label class="bottom-row">
                        <input id="border-bottom-input" class="form-control" tabindex="7"
                               name="{$propname}[border-bottom-width]" value="{$propval['border-bottom-width']}">
                    </label>
                </div>
                <label>
                    <input id="margin-right-input" class="form-control" tabindex="2"
                           name="{$propname}[margin-right]" value="{$propval['margin-right']}">
                </label>
            </div>
            <label class="bottom-row">
                <input id="margin-bottom-input" class="form-control" tabindex="3"
                       name="{$propname}[margin-bottom]" value="{$propval['margin-bottom']}">
            </label>
        </div>
    </div>
    <div class="border-config col-5">
        {include file="./config.select.tpl"
            propname="{$propname}[border-style]"
            propid="{$propname}-border-style"
            propval=$propval['border-style']
            propdesc=[
                'label'   => __('Border style'),
                'options' => [
                    'hidden' => 'versteckt',
                    'dotted' => 'gepunktet',
                    'dashed' => 'gestrichelt',
                    'solid'  => 'durchgezogen'
                ]
            ]}
        {include file="./config.color.tpl"
            propname="{$propname}[border-color]"
            propid="{$propname}-border-color"
            propval=$propval['border-color']
            propdesc=[
                'label'   => __('Border color')
            ]
        }
        <div class='form-group'>
            <label for="config-{$propname}-border-radius">{__('Border radius')}</label>
            <input type="text" class="form-control" id="config-{$propname}-border-radius"
                   name="{$propname}[border-radius]" value="{$propval['border-radius']}">
        </div>
    </div>
</div>