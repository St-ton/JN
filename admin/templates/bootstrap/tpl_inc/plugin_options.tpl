{config_load file="$lang.conf" section='plugin'}
{if $oPlugin !== null}
    <div class="settings-content">
        <form method="post" action="plugin.php?kPlugin={$oPlugin->getID()}" class="navbar-form">
            {$jtl_token}
            <input type="hidden" name="kPlugin" value="{$oPlugin->getID()}" />
            <input type="hidden" name="kPluginAdminMenu" value="{$oPluginAdminMenu->kPluginAdminMenu}" />
            <input type="hidden" name="Setting" value="1" />
            {assign var=open value=0}
            {*$oPluginAdminMenu->kPluginAdminMenu: {$oPluginAdminMenu->kPluginAdminMenu|var_dump}*}
            {*Config:<br>*}
            {*<pre>{$oPlugin->getConfig()->getOptions()|var_dump}</pre>*}
            {foreach $oPlugin->getConfig()->getOptions() as $confItem}
                {if $oPluginAdminMenu->kPluginAdminMenu !== $confItem->menuID}
                    {continue}
                {/if}
                {*{foreach $oPlugin->oPluginEinstellung_arr as $oPluginEinstellung}*}
                    {*{if $oPluginEinstellung->name == $confItem->cWertName}*}
                        {*{assign var=cEinstellungWert value=$oPluginEinstellung->cWert}*}
                    {*{/if}*}
                {*{/foreach}*}
                {if $confItem->confType === Plugin\ExtensionData\Config::TYPE_NOT_CONFIGURABLE}
                    {if $open > 0}
                        </div><!-- .panel-body -->
                        </div><!-- .panel -->
                    {/if}
                    <div class="panel panel-default panel-idx-{$confItem@index}{if $confItem@index === 0} first{/if}">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$confItem->niceName}
                            {if $confItem->description|strlen > 0}
                                <span class="panel-title-addon">{getHelpDesc cDesc=$confItem->description}</span>
                            {/if}
                        </h3>
                    </div>
                    <div class="panel-body">
                    {assign var=open value=1}
                {elseif $confItem->inputType === Plugin\Admin\InputType::NONE}
                    <!-- not showing {$confItem->valueID} -->
                {else}
                    {if $open === 0 && $confItem@index === 0}
                        <div class="panel panel-default first">
                        <div class="panel-heading"><h3 class="panel-title">{#settings#}</h3></div>
                        <div class="panel-body">
                        {assign var=open value=1}
                    {/if}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="{$confItem->valueID}">{$confItem->niceName}</label>
                        </span>
                        <span class="input-group-wrap">
                        {if $confItem->inputType === Plugin\Admin\InputType::SELECT}
                            <select id="{$confItem->valueID}" name="{$confItem->valueID}{if $confItem->confType === Plugin\ExtensionData\Config::TYPE_DYNAMIC}[]{/if}" class="form-control combo"{if $confItem->confType === Plugin\ExtensionData\Config::TYPE_DYNAMIC} multiple{/if}>
                                {foreach $confItem->options as $option}
                                    {if $confItem->confType === Plugin\ExtensionData\Config::TYPE_DYNAMIC && $confItem->value|is_array}
                                        {assign var=selected value=($option->value|in_array:$confItem->value)}
                                    {else}
                                        {assign var=selected value=($confItem->value == $option->value)}
                                    {/if}
                                    <option value="{$option->value}"{if $selected} selected{/if}>{$option->niceName}</option>
                                {/foreach}
                            </select>
                        {elseif $confItem->inputType === Plugin\Admin\InputType::COLORPICKER}
                            <div id="{$confItem->valueID}" style="display:inline-block">
                                <div style="background-color: {$confItem->value}" class="colorSelector"></div>
                            </div>
                            <input type="hidden" name="{$confItem->valueID}" class="{$confItem->valueID}_data" value="{$confItem->value}" />
                            <script type="text/javascript">
                                $('#{$confItem->valueID}').ColorPicker({ldelim}
                                    color:    '{$confItem->value}',
                                    onShow:   function (colpkr) {ldelim}
                                        $(colpkr).fadeIn(500);
                                        return false;
                                    {rdelim},
                                    onHide:   function (colpkr) {ldelim}
                                        $(colpkr).fadeOut(500);
                                        return false;
                                    {rdelim},
                                    onChange: function (hsb, hex, rgb) {ldelim}
                                        $('#{$confItem->valueID} div').css('backgroundColor', '#' + hex);
                                        $('.{$confItem->valueID}_data').val('#' + hex);
                                    {rdelim}
                                {rdelim});
                            </script>
                        {elseif $confItem->inputType === Plugin\Admin\InputType::PASSWORD}
                            <input autocomplete="off" class="form-control" id="{$confItem->valueID}" name="{$confItem->valueID}" type="password" value="{$confItem->value}" />
                        {elseif $confItem->inputType === Plugin\Admin\InputType::TEXTAREA}
                            <textarea class="form-control" id="{$confItem->valueID}" name="{$confItem->valueID}">{$confItem->value}</textarea>
                        {elseif $confItem->inputType === Plugin\Admin\InputType::NUMBER || $confItem->inputType === 'zahl'}
                            <input class="form-control" type="number" name="{$confItem->valueID}" id="{$confItem->valueID}" value="{$confItem->value}" />
                        {elseif $confItem->inputType === Plugin\Admin\InputType::CHECKBOX}
                            <div class="input-group-checkbox-wrap">
                                <input class="form-control" id="{$confItem->valueID}" type="checkbox" name="{$confItem->valueID}"{if $confItem->value === 'on'} checked="checked"{/if}>
                            </div>
                        {elseif $confItem->inputType === Plugin\Admin\InputType::RADIO}
                            <div class="input-group-checkbox-wrap">
                            {foreach $confItem->options as $option}
                                <input id="opt-{$option->id}-{$option@iteration}"
                                       type="radio" name="{$confItem->valueID}[]"
                                       value="{$option->value}"{if $confItem->value == $option->cWert} checked="checked"{/if} />
                                <label for="opt-{$option->kPluginEinstellungenConf}-{$option@iteration}">
                                    {$option->niceName}
                                </label> <br />
                            {/foreach}
                        </div>
                        {else}
                            <input class="form-control" id="{$confItem->valueID}" name="{$confItem->valueID}" type="text" value="{$confItem->value|escape:'html'}" />
                        {/if}
                        </span>
                        {if $confItem->description|strlen > 0}
                            <span class="input-group-addon">{getHelpDesc cDesc=$confItem->description}</span>
                        {/if}
                    </div>
                {/if}
            {/foreach}
            {if $open > 0}
                </div><!-- .panel-body -->
                </div><!-- .panel -->
            {/if}
            <button name="speichern" type="submit" value="{#pluginSettingSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#pluginSettingSave#}</button>
        </form>
    </div>
{/if}
