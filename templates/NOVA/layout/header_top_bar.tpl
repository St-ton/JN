{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-top-bar'}
    {strip}
        {buttongroup size="sm"}
        {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1 || isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
            {block name='layout-header-top-bar-user-settings'}
                {block name='layout-header-top-bar-user-settings-currency'}
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
                {block name='layout-header-top-bar-user-settings-language'}
                    {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                        {dropdown
                            id="language-dropdown"
                            class="language-dropdown"
                            variant="link btn-sm"
                            text="
                                {foreach $smarty.session.Sprachen as $Sprache}
                                    {if $Sprache->kSprache == $smarty.session.kSprache}
                                        {$Sprache->displayLanguage}
                                    {/if}
                                {/foreach}"
                        }
                            {foreach $smarty.session.Sprachen as $oSprache}
                                {dropdownitem href="{$oSprache->cURL}" rel="nofollow" }
                                    {$oSprache->displayLanguage}
                                {/dropdownitem}
                            {/foreach}
                        {/dropdown}
                    {/if}
                {/block}
            {/block}
        {/if}
        {if $linkgroups->getLinkGroupByTemplate('Kopf') !== null}
            {block name='layout-header-top-bar-cms-pages'}
                {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                    {link class="btn btn-link btn-sm" active=$Link->getIsActive() href=$Link->getURL() title=$Link->getTitle()}
                        {$Link->getName()}
                    {/link}
                {/foreach}
            {/block}
        {/if}
        {/buttongroup}
    {/strip}
{/block}
