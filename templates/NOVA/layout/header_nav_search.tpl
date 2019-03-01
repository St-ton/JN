{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{strip}
    {block name='navbar-productsearch'}
        {navform id="search" action="index.php" method="get"}
            {inputgroup class="mx-auto w-maxcon"}
                {input name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                {inputgroupaddon append=true}
                    {button type="submit" variant="light" name="search" id="search-submit-button" aria=["label"=>"{lang key='search'}"]}
                        <span class="fa fa-search"></span>
                    {/button}
                {/inputgroupaddon}
            {/inputgroup}
        {/navform}
    {/block}
{/strip}
