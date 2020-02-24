{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-nav-search'}
    {block name='layout-header-nav-search-search'}
        <li class="nav-item d-none d-lg-block mr-3">
            {form action='index.php' method='get' slide=false}
                <div class="form-icon">
                    {inputgroup}
                        {input id="search-header" name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                        {inputgroupaddon append=true}
                            {button type="submit" name='search' variant="secondary" aria=["label"=>{lang key='search'}]}<span class="fas fa-search"></span>{/button}
                        {/inputgroupaddon}
                    {/inputgroup}
                </div>
            {/form}
        </li>
    {/block}
    {block name='layout-header-nav-search-search-dropdown'}
        {if $Einstellungen.template.theme.mobile_search_type === 'dropdown'}
            {navitemdropdown class='d-block d-lg-none'
                text='<i class="fas fa-search"></i>'
                right=true
                no-caret=true
                router-aria=['label'=>{lang key='findProduct'}]}
                <div class="dropdown-body">
                    {include file='snippets/search_form.tpl' id='search-header-desktop'}
                </div>
            {/navitemdropdown}
        {/if}
    {/block}
{/block}
