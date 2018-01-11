<ul role="tablist" class="nav nav-tabs" id="portlet-design-tab">
    <li class="active" role="presentation"><a aria-expanded="true" aria-controls="general" data-toggle="tab" role="tab" id="general-tab" href="#general">General</a></li>
    <li role="presentation"><a aria-expanded="true" aria-controls="wow-animation" data-toggle="tab" role="tab" id="wow-animation-tab" href="#wow-animation">Animation</a></li>
    <li role="presentation" class=""><a aria-controls="style-design" data-toggle="tab" id="style-design-tab" role="tab" href="#style-design" aria-expanded="false">Style</a></li>
</ul>
<div class="tab-content" id="portlet-design-tab-content">
    <div id="general" class="tab-pane fade active in" role="general" aria-labelledby="general-tab">
        <div class="row">
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="title-flag" name="title-flag" value="1" {if $properties['title-flag'] === '1'} checked="checked"{/if}> Titel des Panels anzeigen?
                    </label>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="footer-flag" name="footer-flag" value="1" {if $properties['footer-flag'] === '1'} checked="checked"{/if}> Fußzeile des Panels anzeigen?
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="config-panel-state">Status</label>
                    <select name="panel-state" class="form-control" id="config-panel-state">
                        <option value="default"{if $properties['panel-state'] === 'default'} selected{/if}>Default</option>
                        <option value="primary"{if $properties['panel-state'] === 'primary'} selected{/if}>Primary</option>
                        <option value="success"{if $properties['panel-state'] === 'success'} selected{/if}>Success</option>
                        <option value="info"{if $properties['panel-state'] === 'info'} selected{/if}>Info</option>
                        <option value="warning"{if $properties['panel-state'] === 'warning'} selected{/if}>Warning</option>
                        <option value="danger"{if $properties['panel-state'] === 'danger'} selected{/if}>Danger</option>
                    </select>
                </div>
            </div>
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