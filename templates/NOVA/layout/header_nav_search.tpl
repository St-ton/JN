{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='layout-header-nav-search'}
    {block name='layout-header-nav-search-search'}
        <li class="nav-item d-none d-lg-block">
            {form action='index.php' method='get'}
                <div class="form-icon">
                    {inputgroup}
                        {input id="search-header" name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                        {inputgroupaddon append=true}
                            {button type="submit" name='search' variant="secondary"}<span class="fas fa-search"></span>{/button}
                        {/inputgroupaddon}
                    {/inputgroup}
                </div>
            {/form}
        </li>
    {/block}
    {block name='layout-header-nav-search-search-dropdown'}
        {navitemdropdown class='d-block d-lg-none'
            text='<i class="fas fa-search"></i>'
            right=true
            no-caret=true
            router-aria=['label'=>{lang key='findProduct'}]}
            <div class="dropdown-body">
                {form action='index.php' method='get'}
                    {input name="qs" type="text" class="ac_input w-100" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                    {button class="mt-3" type="submit" size="sm" variant="primary" block=true name="search" id="search-submit-button" aria=["label"=>"{lang key='search'}"]}
                        {lang key='search'}
                    {/button}
                {/form}
            </div>
        {/navitemdropdown}
    {/block}
{/block}
