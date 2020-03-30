{block name='layout-header-top-bar'}
    {strip}
        {nav tag='ul' class='nav-dividers'}
        {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1 || isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
            {block name='layout-header-top-bar-user-settings'}
                {block name='layout-header-top-bar-user-settings-currency'}
                    {if isset($smarty.session.Waehrungen) && $smarty.session.Waehrungen|@count > 1}
                        {navitemdropdown
                            class="currency-dropdown"
                            right=true
                            text=$smarty.session.Waehrung->getName()
                        }
                            {foreach $smarty.session.Waehrungen as $currency}
                                {dropdownitem href=$currency->getURLFull() rel="nofollow" active=($smarty.session.Waehrung->getName() === $currency->getName())}
                                    {$currency->getName()}
                                {/dropdownitem}
                            {/foreach}
                        {/navitemdropdown}
                    {/if}
                {/block}
                {block name='layout-header-top-bar-user-settings-language'}
                    {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                        {navitemdropdown
                            class="language-dropdown"
                            right=true
                            text="
                                {foreach $smarty.session.Sprachen as $language}
                                    {if $language->kSprache == $smarty.session.kSprache}
                                        {$language->iso639|upper}
                                    {/if}
                                {/foreach}"
                        }
                            {foreach $smarty.session.Sprachen as $language}
                                {dropdownitem href="{$language->cURL}" rel="nofollow" active=($language->kSprache == $smarty.session.kSprache)}
                                    {$language->iso639|upper}
                                {/dropdownitem}
                            {/foreach}
                        {/navitemdropdown}
                    {/if}
                {/block}
            {/block}
        {/if}
        {if $linkgroups->getLinkGroupByTemplate('Kopf') !== null && $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG}
            {block name='layout-header-top-bar-cms-pages'}
                {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                    {navitem active=$Link->getIsActive() href=$Link->getURL() title=$Link->getTitle()}
                        {$Link->getName()}
                    {/navitem}
                {/foreach}
            {/block}
        {/if}
        {/nav}
    {/strip}
{/block}
