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
                <label>Layout</label>
                <div class="checkbox">
                    <label>
                        <input type="radio"
                               id="layout-button"
                               name="layout"
                               value="button" {if $properties['layout'] === 'button'}
                               checked="checked"{/if}> Button with collapse area
                    </label>
                    <label>
                        <input type="radio"
                               id="layout-panel"
                               name="layout"
                               value="panel" {if $properties['layout'] === 'panel'}
                               checked="checked"{/if}> accordion with panel component
                    </label>
                </div>
            </div>
            <div class="col-sm-6">
                <label>Standardanzeige</label>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"
                               name="cllps-initial-state"
                               value="in" {if $properties['cllps-initial-state'] === 'in'}
                               checked="checked"{/if}> ausgeklappt
                        <span id="helpBlock"
                              class="help-block">
                            In der Vorschau und beim Bearbeiten ist wird der Bereich immer angezeigt.
                        </span>
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

        <div id="cllps-button-container" {if $properties['layout'] === 'panel'}style="display:none;"{/if}>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="cllps-button-text">Text</label>
                        <input name="cllps-button-text"
                               value="{$properties['cllps-button-text']}"
                               class="form-control"
                               id="cllps-button-text">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="cllps-button-type">Type</label>
                        <select class="form-control" id="cllps-button-type" name="cllps-button-type">
                            <option value="default"{if $properties['cllps-button-type'] === 'default'} selected{/if}>Default</option>
                            <option value="primary"{if $properties['cllps-button-type'] === 'primary'} selected{/if}>Primary</option>
                            <option value="success"{if $properties['cllps-button-type'] === 'success'} selected{/if}>Success</option>
                            <option value="info"{if $properties['cllps-button-type'] === 'info'} selected{/if}>Info</option>
                            <option value="warning"{if $properties['cllps-button-type'] === 'warning'} selected{/if}>Warning</option>
                            <option value="danger"{if $properties['cllps-button-type'] === 'danger'} selected{/if}>Danger</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="cllps-button-size">Size</label>
                        <select class="form-control" id="cllps-button-size" name="cllps-button-size">
                            <option value="xs"{if $properties['cllps-button-size'] === 'xs'} selected{/if}>Mini</option>
                            <option value="sm"{if $properties['cllps-button-size'] === 'sm'} selected{/if}>Small</option>
                            <option value="md"{if $properties['cllps-button-size'] === 'md'} selected{/if}>Normal</option>
                            <option value="lg"{if $properties['cllps-button-size'] === 'lg'} selected{/if}>Large</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(function(){
                $('input[name="layout"]').click(function(){
                    if ($(this).val() == 'button'){
                        $('#cllps-button-container').show();
                    }else{
                        $('#cllps-button-container').hide();
                    }
                });
            });
        </script>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>