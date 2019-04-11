{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-categories-offcanvas'}
    {lang key='view' assign='view_'}
    {block name='snippets-categories-offcanvas-heading'}
        <div class="h5">{$result->current->cName}</div>
    {/block}
    {nav}
        {block name='snippets-categories-offcanvas-navitems'}
            {navitem class="clearfix"}
                {link href="#" class="nav-sub pull-left" data-ref="0"}
                    <i class="fa fa-bars"></i> {lang key='showAll'}
                {/link}
                {link href="#" class="nav-sub pull-right" data-ref=$result->current->kOberKategorie}
                    <i class="fa fa-backward"></i> {lang key='back'}
                {/link}
            {/navitem}
            {navitem}
                {link href=$result->current->cURL class="nav-active"}
                    {$result->current->cName} {$view_|lower}
                {/link}
            {/navitem}
        {/block}
        {block name='snippets-categories-offcanvas-include-categories-recursive'}
            {include file='snippets/categories_recursive.tpl' i=0 categoryId=$result->current->kKategorie limit=2 caret='right'}
        {/block}
    {/nav}
{/block}
