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
                    <label for="config-row-layout">Layout</label>
                    <input type="text" id="config-row-layout" name="layout" class="form-control" placeholder="6+6" value="{$properties.layout}">
                    <span class="help-block">Geben Sie die Spaltenbreiten in der Form '6+3+3' oder '4+4+4' an. Werte von 1 bis 12 sind möglich.</span>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-heading-class">Class</label>
                    <input name="attr[class]" value="{$properties.attr['class']}" class="form-control" id="config-heading-class">
                </div>
            </div>
        </div>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>
