<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <label>Layout</label>
                <div class="checkbox">
                    <label>
                        <input type="radio"
                               id="listType-ol"
                               name="listType"
                               value="ol" {if $properties['listType'] === 'ol'}
                            checked="checked"{/if}> geordnete Liste
                    </label>
                    <label>
                        <input type="radio"
                               id="listType-ul"
                               name="listType"
                               value="ul" {if $properties['listType'] === 'ul'}
                            checked="checked"{/if}> ungeordnete Liste
                    </label>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="list-count">Anzahl Elemente</label>
                    <input type="number" name="count" value="{$properties['count']}" class="form-control" id="list-count">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="list-style-type">Type</label>
                    <select class="form-control" id="list-style-type" name="list-style-type">
                        <option value=""{if $properties['list-style-type'] === ''} selected{/if}>Default</option>
                        <option value="decimal-leading-zero"{if $properties['list-style-type'] === 'decimal-leading-zero'} selected{/if}>decimal-leading-zero</option>
                        <option value="lower-latin"{if $properties['list-style-type'] === 'lower-latin'} selected{/if}>lower-latin</option>
                        <option value="lower-roman"{if $properties['list-style-type'] === 'lower-roman'} selected{/if}>lower-roman</option>
                        <option value="upper-latin"{if $properties['list-style-type'] === 'upper-latin'} selected{/if}>upper-latin</option>
                        <option value="upper-roman"{if $properties['list-style-type'] === 'upper-roman'} selected{/if}>upper-roman</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tabs-class">Class</label>
                    <input name="attr[class]" value="{$properties.attr['class']}" class="form-control" id="tabs-class">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tabs-id">ID</label>
                    <input name="attr[id]" value="{$properties.attr['id']}" class="form-control" id="tabs-id">
                </div>
            </div>
        </div>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>