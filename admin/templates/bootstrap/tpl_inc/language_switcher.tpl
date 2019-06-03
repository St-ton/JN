{assign var=onchange value=$onchange|default:true}
{assign var=id value=$id|default:'lang-switcher'}
<span class="input-group-addon">
    <label for="{$id}">{__('changeLanguage')}</label>
</span>
<span class="input-group-wrap last">
    <select id="{$id}" name="kSprache" class="form-control selectBox"{if $onchange} onchange="document.sprache.submit();"{/if}>
        {foreach $sprachen as $language}
            <option value="{$language->getID()}" {if $language->getID() === $smarty.session.kSprache}{assign var=currentLanguage value=$language->getLocalizedName()}selected{/if}>{$language->getLocalizedName()}</option>
        {/foreach}
    </select>
</span>
