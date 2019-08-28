{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-nav-search'}
    {strip}
        {block name='layout-header-nav-search-form'}
            {navform id="search" action="index.php" method="get" class="py-3 py-md-0"}
                {block name='layout-header-nav-search-form-content'}
                    {inputgroup class="mx-auto w-maxcon"}
                        {input name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                        {inputgroupaddon append=true}
                            {button type="submit" variant="light" name="search" id="search-submit-button" aria=["label"=>"{lang key='search'}"]}
                                <span class="fas fa-search"></span>
                            {/button}
                        {/inputgroupaddon}
                    {/inputgroup}
                {/block}
            {/navform}
        {/block}
    {/strip}
{/block}
