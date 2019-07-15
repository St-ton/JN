{assign var=onchange value=$onchange|default:true}
{assign var=id value=$id|default:'lang-switcher'}
<form name="sprache" method="post" action="{$action|default:''}" class="inline_block">
    {$jtl_token}
    <input type="hidden" name="sprachwechsel" value="1" />
    <div class="input-group left mr-3">
        <span class="input-group-addon">
            <label for="{$id}">{__('changeLanguage')}:</label>
        </span>
        <span class="label-wrap last">
            <select id="{$id}" name="kSprache" class="custom-select selectBox"{if $onchange} onchange="document.sprache.submit();"{/if}>
                {foreach $sprachen as $language}
                    <option value="{$language->getId()}" {if $language->getId() === $smarty.session.kSprache}{assign var=currentLanguage value=$language->getLocalizedName()}selected{/if}>{$language->getLocalizedName()}</option>
                {/foreach}
            </select>
        </span>
    </div>
</form>
