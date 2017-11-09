<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation" class=""><a aria-controls="icon" data-toggle="tab" id="icon-tab" role="tab" href="#icon" aria-expanded="false">Icon</a></li>
    <li role="presentation" class=""><a aria-controls="url-link" data-toggle="tab" id="url-link-tab" role="tab" href="#url-link" aria-expanded="false">Url (link)</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="form-group">
            <label for="button-text">Text</label>
            <input type="text" id="button-text" name="button-text" class="form-control" placeholder="Button text" value="{$properties['button-text']}">
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-type">Type</label>
                    <select class="form-control" id="button-type" name="button-type">
                        <option value="default"{if $properties['button-type'] === 'default'} selected{/if}>Default</option>
                        <option value="primary"{if $properties['button-type'] === 'primary'} selected{/if}>Primary</option>
                        <option value="success"{if $properties['button-type'] === 'success'} selected{/if}>Success</option>
                        <option value="info"{if $properties['button-type'] === 'info'} selected{/if}>Info</option>
                        <option value="warning"{if $properties['button-type'] === 'warning'} selected{/if}>Warning</option>
                        <option value="danger"{if $properties['button-type'] === 'danger'} selected{/if}>Danger</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-size">Size</label>
                    <select class="form-control" id="button-size" name="button-size">
                        <option value="xs"{if $properties['button-size'] === 'xs'} selected{/if}>Mini</option>
                        <option value="sm"{if $properties['button-size'] === 'sm'} selected{/if}>Small</option>
                        <option value="md"{if $properties['button-size'] === 'md'} selected{/if}>Normal</option>
                        <option value="lg"{if $properties['button-size'] === 'lg'} selected{/if}>Large</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-alignment">Alignment</label>
                    <select class="form-control" id="button-alignment" name="button-alignment">
                        <option value="inline"{if $properties['button-alignment'] === 'inline'} selected{/if}>Inline</option>
                        <option value="left"{if $properties['button-alignment'] === 'left'} selected{/if}>Left</option>
                        <option value="right"{if $properties['button-alignment'] === 'right'} selected{/if}>Right</option>
                        <option value="center"{if $properties['button-alignment'] === 'center'} selected{/if}>Center</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="button-full-width-flag">Full width?</label>
                    <div class="radio" id="button-full-width-flag">
                        <label class="radio-inline">
                            <input type="radio" name="button-full-width-flag" id="button-full-width-flag-0" value="no"{if $properties['button-full-width-flag'] === 'no'} checked="checked"{/if}> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="button-full-width-flag" id="button-full-width-flag-1" value="yes"{if $properties['button-full-width-flag'] === 'yes'} checked="checked"{/if}> Yes
                        </label>

                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="class">Class name</label>
            <input type="text"  id="button-class" name="button-class" class="form-control" value="{$properties['button-class']}">
        </div>
    </div>
    {include file='./settings.tabcontent.icon.tpl'}
    {include file='./settings.tabcontent.url.tpl'}
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>