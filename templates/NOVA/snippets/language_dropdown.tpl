{block name='snippets-language-dropdown'}
    {$languages = JTL\Session\Frontend::getLanguages()}
    {if $languages|count > 1}
        {capture langSelectorText}
            {foreach $languages as $language}
                {if $language->getId() === JTL\Shop::getLanguageID()}
                    {block name='snippets-language-dropdown-text'}
                        {$language->getIso639()|upper}
                    {/block}
                {/if}
            {/foreach}
        {/capture}
        {navitemdropdown
        class="language-dropdown {$dropdownClass|default:''}"
        right=true
        text=$smarty.capture.langSelectorText}
            {foreach $languages as $language}
                {block name='snippets-language-dropdown-item'}
                    {dropdownitem href="{$language->getUrl()}"
                        class="link-lang"
                        data=["iso"=>$language->getIso()]
                        rel="nofollow"
                        active=($language->getId() === JTL\Shop::getLanguageID())}
                        {$language->getIso639()|upper}
                    {/dropdownitem}
                {/block}
            {/foreach}
        {/navitemdropdown}
    {/if}
{/block}
