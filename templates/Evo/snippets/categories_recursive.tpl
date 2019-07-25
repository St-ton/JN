{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if (!empty($categories) ||isset($categoryId)) && (!isset($i) || isset($i) && isset($limit) && $i < $limit)}
    {strip}
        {if !isset($i)}
            {assign var='i' value=0}
        {/if}
        {if !isset($limit)}
            {assign var='limit' value=3}
        {/if}
        {if !isset($caret)}
            {assign var='caret' value='down'}
        {/if}
        {if !isset($activeId)}
            {assign var='activeId' value='0'}
            {if $NaviFilter->hasCategory()}
                {assign var='activeId' value=$NaviFilter->getCategory()->getValue()}
            {elseif $nSeitenTyp == 1 && isset($Artikel)}
                {assign var='activeId' value=$Artikel->gibKategorie()}
            {elseif $nSeitenTyp == 1 && isset($smarty.session.LetzteKategorie)}
                {assign var='activeId' value=$smarty.session.LetzteKategorie}
            {/if}
        {/if}
        {if !isset($activeParents)
        && ($nSeitenTyp === $smarty.const.PAGE_ARTIKEL || $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
            {get_category_parents categoryId=$activeId assign='activeParents'}
        {/if}
        {if !isset($activeParents)}
            {assign var='activeParents' value=null}
        {/if}
        {if empty($categories)}
            {if !isset($categoryBoxNumber)}
                {assign var='categoryBoxNumber' value=null}
            {/if}
            {get_category_array categoryId=$categoryId categoryBoxNumber=$categoryBoxNumber assign='categories'}
        {/if}
        {if !empty($categories)}
            {foreach $categories as $category}
                {assign var='hasItems' value=$category->hasChildren() && (($i+1) < $limit)}
                {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                    {assign var='activeParent' value=$activeParents[$i]}
                {/if}
                <li{if $category->getID() == $activeId || ((isset($activeParent) && isset($activeParent->kKategorie)) && $activeParent->kKategorie == $category->getID())} class="active"{/if}>
                    <a href="{$category->getURL()}"{if $hasItems} class="nav-sub"{/if} data-ref="{$category->getID()}">
                        {$category->getShortName()}
                        {if $hasItems}<i class="fa fa-caret-{$caret} nav-toggle pull-right"></i>{/if}
                    </a>
                    {if $hasItems}
                        <ul class="nav">
                            {if !empty($category->getChildren())}
                                {include file='snippets/categories_recursive.tpl' i=$i+1 categories=$category->getChildren() limit=$limit activeId=$activeId activeParents=$activeParents}
                            {else}
                                {include file='snippets/categories_recursive.tpl' i=$i+1 categoryId=$category->getID() limit=$limit categories=null activeId=$activeId activeParents=$activeParents}
                            {/if}
                        </ul>
                    {/if}
                </li>
            {/foreach}
        {/if}
    {/strip}
{/if}
