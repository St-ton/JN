{block name='layout-header-top-bar'}
    {strip}
        {nav tag='ul' class='topbar-main nav-dividers'}
            {block name='layout-header-top-bar-user-settings'}
                {block name='layout-header-top-bar-user-settings-currency'}
                    {$allCurrencies = JTL\Session\Frontend::getCurrencies()}
                    {$currentCurrency = JTL\Session\Frontend::getCurrency()}
                    {if $allCurrencies|count > 1}
                        {navitemdropdown
                            class="currency-dropdown"
                            right=true
                            text=$currentCurrency->getName()
                        }
                            {foreach $allCurrencies as $currency}
                                {dropdownitem href=$currency->getURLFull() rel="nofollow" active=($currentCurrency->getName() === $currency->getName())}
                                    {$currency->getName()}
                                {/dropdownitem}
                            {/foreach}
                        {/navitemdropdown}
                    {/if}
                {/block}
                {block name='layout-header-top-bar-user-settings-include-language-dropdown'}
                    {include file='snippets/language_dropdown.tpl'}
                {/block}
            {/block}
        {if $linkgroups->getLinkGroupByTemplate('Kopf') !== null && $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}
            {block name='layout-header-top-bar-cms-pages'}
                {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                    {navitem active=$Link->getIsActive() href=$Link->getURL() title=$Link->getTitle() target=$Link->getTarget()}
                        {$Link->getName()}
                    {/navitem}
                {/foreach}
            {/block}
        {/if}
        {/nav}
        {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}
            {block name='layout-header-top-bar-note'}
                {$topbarLang = {lang key='topbarNote'}}
                {if $topbarLang !== ''}
                    {nav tag='ul' class='topbar-note nav-dividers'}
                        {navitem id="topbarNote"}{$topbarLang}{/navitem}
                    {/nav}
                {/if}
            {/block}
        {/if}
    {/strip}
{/block}
