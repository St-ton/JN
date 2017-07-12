{strip}
{if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1 || isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
    {block name="top-bar-user-settings"}
    <ul class="list-inline user-settings pull-right">
        {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
            <li class="currency-dropdown dropdown">
                <a href="#" class="dropdown-toggle btn btn-default btn-xs" data-toggle="dropdown">
                    {if $smarty.session.Waehrung->getCode() === 'EUR'}
                        <i class="fa fa-eur" title="{$smarty.session.Waehrung->getName()}"></i>
                    {elseif $smarty.session.Waehrung->getCode() === 'USD'}
                        <i class="fa fa-usd" title="{$smarty.session.Waehrung->getName()}"></i>
                    {elseif $smarty.session.Waehrung->getCode() === 'GBP'}
                        <i class="fa fa-gbp" title="{$smarty.session.Waehrung->getName()}"></i>
                    {else}
                        {$smarty.session.Waehrung->getName()}
                    {/if} <span class="caret"></span></a>
                <ul id="currency-dropdown" class="dropdown-menu dropdown-menu-right">
                {foreach from=$smarty.session.Waehrungen item=oWaehrung}
                    <li>
                        <a href="{$oWaehrung->getURL()}" rel="nofollow">{$oWaehrung->getName()}</a>
                    </li>
                {/foreach}
                </ul>
            </li>
        {/if}
        {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
        <li class="language-dropdown dropdown">
            <a href="#" class="dropdown-toggle btn btn-default btn-xs" data-toggle="dropdown" itemprop="inLanguage" itemscope itemtype="http://schema.org/Language">
                <i class="fa fa-language"></i>
                {foreach from=$smarty.session.Sprachen item=Sprache}
                    {if $Sprache->kSprache == $smarty.session.kSprache}
                        <span class="lang-{$lang}" itemprop="name"> {if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</span>
                    {/if}
                {/foreach}
                <span class="caret"></span>
            </a>
            <ul id="language-dropdown" class="dropdown-menu dropdown-menu-right">
            {foreach from=$smarty.session.Sprachen item=oSprache}
                {if $oSprache->kSprache != $smarty.session.kSprache}
                    <li>
                        <a href="{if isset($oSprache->cURLFull)}{$oSprache->cURLFull}{else}{$oSprache->cURL}{/if}" class="link_lang {$oSprache->cISO}" rel="nofollow">{if $lang === 'ger'}{$oSprache->cNameDeutsch}{else}{$oSprache->cNameEnglisch}{/if}</a>
                    </li>
                {/if}
                {/foreach}
            </ul>
        </li>
        {* /language-dropdown *}
        {/if}
    </ul>{* user-settings *}
    {/block}
{/if}
{if isset($linkgroups->Kopf) && $linkgroups->Kopf}
<ul class="cms-pages list-inline pull-right">
    {block name="top-bar-cms-pages"}
        {foreach name=headlinks from=$linkgroups->Kopf->Links item=Link}
            {if $Link->cLocalizedName|has_trans}
                <li class="{if isset($Link->aktiv) && $Link->aktiv == 1}active{/if}">
                    <a href="{$Link->URL}"{if $Link->cNoFollow == 'Y'} rel="nofollow"{/if}>{$Link->cLocalizedName|trans}</a>
                </li>
            {/if}
        {/foreach}
    {/block}
</ul>
{/if}
{/strip}