<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <input type="hidden" id="uid" name="uid" value="{$properties['uid']}">
        <div class="row">
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="radio" id="layout-button" name="layout" value="button" {if $properties['layout'] === 'button'} checked="checked"{/if}> Button with collapse area
                    </label>
                    <label>
                        <input type="radio" id="layout-panel" name="layout" value="panel" {if $properties['layout'] === 'panel'} checked="checked"{/if}> accordeon with panel component
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-panel-class">Class</label>
                    <input name="attr[class]" value="{$properties.attr['class']}" class="form-control" id="config-panel-class">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-panel-id">ID</label>
                    <input name="attr[id]" value="{$properties.attr['id']}" class="form-control" id="config-panel-id">
                </div>
            </div>
        </div>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>