{$skipHeading = $skipHeading|default:false}
<form{if !empty($name)} name="{$name}"{/if} method="{if !empty($method)}{$method}{else}post{/if}"{if !empty($action)} action="{$action}"{/if}>
    {$jtl_token}
    <input type="hidden" name="einstellungen" value="1" />
    {if !empty($a)}
        <input type="hidden" name="a" value="{$a}" />
    {/if}
    {if !empty($tab)}
        <input type="hidden" name="tab" value="{$tab}" />
    {/if}
    <div class="settings">
        {if !empty($title)}
            <span class="subheading1">{$title}</span>
            <hr class="mb-3">
        {/if}
        <div>
            {include file='tpl_inc/config_sections.tpl' sections=$sections|default:([$section])}
            {$additional|default:''}
        </div>
        <div class="save-wrapper card-footer">
            <div class="row">
                {$additionalButtons|default:''}
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="speichern" type="submit" class="btn btn-primary btn-block">
                        {if !empty($buttonCaption)}{$buttonCaption}{else}{__('saveWithIcon')}{/if}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
