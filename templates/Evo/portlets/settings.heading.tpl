<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="form-group">
            <label for="config-heading-level">Level</label>
            <select name="level" class="form-control" id="config-heading-level">
                <option value="1"{if $properties.level === '1'} selected{/if}>h1</option>
                <option value="2"{if $properties.level === '2'} selected{/if}>h2</option>
                <option value="3"{if $properties.level === '3'} selected{/if}>h3</option>
                <option value="4"{if $properties.level === '4'} selected{/if}>h4</option>
                <option value="5"{if $properties.level === '5'} selected{/if}>h5</option>
                <option value="6"{if $properties.level === '6'} selected{/if}>h6</option>
            </select>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-heading-text">Text</label>
                    <input name="text" value="{$properties.text}" class="form-control" id="config-heading-text">
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