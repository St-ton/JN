{assign var=onchange value=$onchange|default:true}
{assign var=id value=$id|default:'lang-switcher'}
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
