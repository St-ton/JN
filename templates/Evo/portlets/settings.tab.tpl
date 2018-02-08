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
        <div id="singleTabSettings">
            {foreach name=tabs item=tab from=$properties.tab}
                {assign var=index value=$smarty.foreach.tabs.iteration}
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="tabs-class">Tab{$index} Name</label>
                            <input name="tab[{$index}]" value="{$properties.tab[$index]}" class="form-control" id="tab[{$index}]">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label>Standardanzeige</label>
                        <div class="checkbox">
                            <label>
                                <input type="radio"
                                       name="active"
                                       value="{$index}" {if $properties.active == $index}
                                       checked="checked"{/if}> aktiv
                            </label>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-success" onclick="addTab();"><i class="glyphicon glyphicon-plus"></i> Tab hinzuf&uuml;gen</button>
        </div>
        <div class="hidden" id="newTabSetting">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="tabs-class">TabNEU Name</label>
                        <input name="tab[NEU]" value="NEU" class="form-control" id="tab[NEU]">
                    </div>
                </div>
                <div class="col-sm-6">
                    <label>Standardanzeige</label>
                    <div class="checkbox">
                        <label>
                            <input type="radio"
                                   name="active"
                                   value="NEU"> aktiv
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var count = {if isset($properties.tab)}{$properties.tab|@count +1}{else}0{/if};
            function addTab() {
                var new_tab = $('#newTabSetting').html();
                new_tab = new_tab.replace(/NEU/g, count);
                $('#singleTabSettings').append( new_tab );
                count++;
            }
        </script>
    </div>
    {include file='./settings.tabcontent.animation.tpl'}
    {include file='./settings.tabcontent.style.tpl'}
</div>