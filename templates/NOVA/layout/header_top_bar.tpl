{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{strip}
    {buttongroup size="sm"}
    {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1 || isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
        {block name='top-bar-user-settings'}
            {block name='top-bar-user-settings-currency'}
                {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
                    {dropdown
                        id="currency-dropdown"
                        variant="link btn-sm"
                        class="currency-dropdown"
                        text="
                            {if $smarty.session.Waehrung->getCode() === 'EUR'}
                                <i class='fas fa-euro-sign' title='{$smarty.session.Waehrung->getName()}'></i>
                            {elseif $smarty.session.Waehrung->getCode() === 'USD'}
                                <i class='fas fa-dollar-sign' title='{$smarty.session.Waehrung->getName()}'></i>
                            {elseif $smarty.session.Waehrung->getCode() === 'GBP'}
                                <i class='fas fa-pound-sign'' title='{$smarty.session.Waehrung->getName()}''></i>
                            {else}
                                {$smarty.session.Waehrung->getName()}
                            {/if}"
                    }
                        {foreach $smarty.session.Waehrungen as $oWaehrung}
                            {dropdownitem href=$oWaehrung->getURLFull() rel="nofollow" }
                                {$oWaehrung->getName()}
                            {/dropdownitem}
                        {/foreach}
                    {/dropdown}
                {/if}
            {/block}
            {block name='top-bar-user-settings-language'}
                {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                    {dropdown
                        id="language-dropdown"
                        class="language-dropdown"
                        variant="link btn-sm"
                        text="
                            {foreach $smarty.session.Sprachen as $Sprache}
                                {if $Sprache->kSprache == $smarty.session.kSprache}
                                    {if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}
                                {/if}
                            {/foreach}"
                    }
                        {foreach $smarty.session.Sprachen as $oSprache}
                            {dropdownitem href="{if isset($oSprache->cURLFull)}{$oSprache->cURLFull}{else}{$oSprache->cURL}{/if}" rel="nofollow" }
                                {if $lang === 'ger'}{$oSprache->cNameDeutsch}{else}{$oSprache->cNameEnglisch}{/if}
                            {/dropdownitem}
                        {/foreach}
                    {/dropdown}
                {/if}
            {/block}
        {/block}
    {/if}
    {if $linkgroups->getLinkGroupByTemplate('Kopf') !== null}
        {block name='top-bar-cms-pages'}
            {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                {link class="btn btn-link btn-sm" active=$Link->getIsActive() href=$Link->getURL() title=$Link->getTitle()}
                    {$Link->getName()}
                {/link}
            {/foreach}
        {/block}
    {/if}
    {/buttongroup}
{/strip}
