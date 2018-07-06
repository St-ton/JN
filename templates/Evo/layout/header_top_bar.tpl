{strip}
{if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1 || isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
    {block name='top-bar-user-settings'}
    <ul class="list-inline user-settings pull-right">
        {block name='top-bar-user-settings-currency'}
        {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
            <li class="currency-dropdown dropdown">
                <a href="#" class="dropdown-toggle btn btn-default btn-xs" data-toggle="dropdown" title="{lang key='selectCurrency'}">
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
                {foreach $smarty.session.Waehrungen as $oWaehrung}
                    <li>
                        <a href="{$oWaehrung->getURL()}" rel="nofollow">{$oWaehrung->getName()}</a>
                    </li>
                {/foreach}
                </ul>
            </li>
        {/if}
        {/block}
        {block name='top-bar-user-settings-language'}
        {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
        <li class="language-dropdown dropdown">
            <a href="#" class="dropdown-toggle btn btn-default btn-xs" data-toggle="dropdown" itemprop="inLanguage" itemscope itemtype="http://schema.org/Language" title="{lang key='selectLang'}">
                <i class="fa fa-language"></i>
                {foreach $smarty.session.Sprachen as $Sprache}
                    {if $Sprache->kSprache == $smarty.session.kSprache}
                        <span class="lang-{$lang}" itemprop="name"> {if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</span>
                    {/if}
                {/foreach}
                <span class="caret"></span>
            </a>
            <ul id="language-dropdown" class="dropdown-menu dropdown-menu-right">
            {foreach $smarty.session.Sprachen as $oSprache}
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
        {/block}
    </ul>{* user-settings *}
    {/block}
{/if}
{if $linkgroups->getLinkGroupByTemplate('Kopf') !== null}
    <ul class="cms-pages list-inline pull-right">
        {block name='top-bar-cms-pages'}
            {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                <li class="{if $Link->getIsActive()}active{/if}">
                    <a href="{$Link->getURL()}"{if $Link->getNoFollow()} rel="nofollow"{/if} title="{$Link->getTitle()}">{$Link->getName()}</a>
                </li>
            {/foreach}
        {/block}
    </ul>
{/if}
{/strip}