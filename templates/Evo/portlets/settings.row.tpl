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
                    <select name="layout" class="form-control" id="config-row-layout">
                        <option value="6,6"{if $properties.layout === '6,6'} selected{/if}>6+6</option>
                        <option value="4,4,4"{if $properties.layout === '4,4,4'} selected{/if}>4+4+4</option>
                        <option value="3,3,3,3"{if $properties.layout === '3,3,3,3'} selected{/if}>3+3+3+3</option>
                    </select>
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
