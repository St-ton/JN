{config_load file="$lang.conf" section='plugin'}
{if $oPlugin !== null}
    <div class="settings-content">
        <form method="post" action="plugin.php?kPlugin={$oPlugin->getID()}" class="navbar-form">
            {$jtl_token}
            <input type="hidden" name="kPlugin" value="{$oPlugin->getID()}" />
            <input type="hidden" name="kPluginAdminMenu" value="{$oPluginAdminMenu->kPluginAdminMenu}" />
            <input type="hidden" name="Setting" value="1" />
            {assign var=open value=0}
            {foreach $oPlugin->getConfig()->getOptions() as $confItem}
                {if $oPluginAdminMenu->kPluginAdminMenu !== $confItem->menuID}
                    {continue}
                {/if}
                {if $confItem->confType === JTL\Plugin\Data\Config::TYPE_NOT_CONFIGURABLE}
                    {if $open > 0}
                        </div><!-- .panel-body -->
                        </div><!-- .panel -->
                    {/if}
                    <div class="panel-idx-{$confItem@index}{if $confItem@index === 0} first{/if} mb-3">
                    <div class="subheading1">{__($confItem->niceName)}
                        {if $confItem->description|strlen > 0}
                            <span class="card-title-addon">{getHelpDesc cDesc=$confItem->description}</span>
                        {/if}
                    </div>
                    <hr>
                    <div class="">
                    {assign var=open value=1}
                {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::NONE}
                    <!-- not showing {$confItem->valueID} -->
                {else}
                    {if $open === 0 && $confItem@index === 0}
                        <div class="first">
                        <div class="subheading1">{__('settings')}</div>
                        <hr class="mb-3">
                        <div>
                        {assign var=open value=1}
                    {/if}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$confItem->valueID}">{__($confItem->niceName)}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        {if $confItem->inputType === JTL\Plugin\Admin\InputType::SELECT}
                            <select id="{$confItem->valueID}"
                                    name="{$confItem->valueID}{if $confItem->confType === JTL\Plugin\Data\Config::TYPE_DYNAMIC}[]{/if}"
                                    class="custom-select"{if $confItem->confType === JTL\Plugin\Data\Config::TYPE_DYNAMIC} multiple="multiple"{/if}
                                    data-selected-text-format="count > 2"
                                    data-size="7"
                                    data-actions-box="true">
                                {foreach $confItem->options as $option}
                                    {if $confItem->confType === JTL\Plugin\Data\Config::TYPE_DYNAMIC && $confItem->value|is_array}
                                        {assign var=selected value=($option->value|in_array:$confItem->value)}
                                    {else}
                                        {assign var=selected value=($confItem->value == $option->value)}
                                    {/if}
                                    <option value="{$option->value}"{if $selected} selected{/if}>{__($option->niceName)}</option>
                                {/foreach}
                            </select>
                        {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::COLORPICKER}
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
                        {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::PASSWORD}
                            <input autocomplete="off" class="form-control" id="{$confItem->valueID}" name="{$confItem->valueID}" type="password" value="{$confItem->value}" />
                        {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::TEXTAREA}
                            <textarea class="form-control" id="{$confItem->valueID}" name="{$confItem->valueID}">{$confItem->value}</textarea>
                        {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::NUMBER || $confItem->inputType === 'zahl'}
                            <div class="input-group form-counter">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                        <span class="fas fa-minus"></span>
                                    </button>
                                </div>
                                <input class="form-control" type="number" name="{$confItem->valueID}" id="{$confItem->valueID}" value="{$confItem->value}" />
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                        <span class="fas fa-plus"></span>
                                    </button>
                                </div>
                            </div>
                        {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::CHECKBOX}
                            <div class="input-group-checkbox-wrap">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input form-control" id="{$confItem->valueID}" type="checkbox" name="{$confItem->valueID}"{if $confItem->value === 'on'} checked="checked"{/if}>
                                    <label class="custom-control-label" for="{$confItem->valueID}"></label>
                                </div>
                            </div>
                        {elseif $confItem->inputType === JTL\Plugin\Admin\InputType::RADIO}
                            <div class="input-group-checkbox-wrap">
                            {foreach $confItem->options as $option}
                                <input id="opt-{$option->id}-{$option@iteration}"
                                       type="radio" name="{$confItem->valueID}[]"
                                       value="{$option->value}"{if $confItem->value == $option->cWert} checked="checked"{/if} />
                                <label for="opt-{$option->kPluginEinstellungenConf}-{$option@iteration}">
                                    {__($option->niceName)}
                                </label> <br />
                            {/foreach}
                        </div>
                        {else}
                            <input class="form-control" id="{$confItem->valueID}" name="{$confItem->valueID}" type="text" value="{$confItem->value|escape:'html'}" />
                        {/if}
                        </div>
                        {if $confItem->description|strlen > 0}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__($confItem->description)}</div>
                        {/if}
                    </div>
                {/if}
            {/foreach}
            {if $open > 0}
                </div><!-- .panel-body -->
                </div><!-- .panel -->
            {/if}
            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                           <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/if}
