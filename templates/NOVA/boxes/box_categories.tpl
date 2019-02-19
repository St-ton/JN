{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card
    class="box box-categories word-break mb-7"
    id="sidebox_categories{$oBox->getCustomID()}"
    title="{if !empty($oBox->getTitle())}{$oBox->getTitle()}{else}{lang key='categories'}{/if}"
}
    <hr class="mt-0 mb-4">
    <div class="nav-panel">
        {nav vertical=true}
            {include file='snippets/categories_recursive.tpl' i=0 categoryId=0 categoryBoxNumber=$oBox->getCustomID() limit=3 categories=$oBox->getItems()}
        {/nav}
    </div>
{/card}
