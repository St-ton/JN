{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-search-form'}
    {form action='index.php' method='get' class='main-search flex-grow-1'}
        {inputgroup}
            {input id="search-header-mobile" name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
            {inputgroupaddon append=true}
                {button type="submit" name='search' variant="secondary"}
                    <span class="fas fa-search"></span>
                {/button}
            {/inputgroupaddon}
        {/inputgroup}
    {/form}
{/block}
