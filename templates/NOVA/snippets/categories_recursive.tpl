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
        {if !isset($activeParents) && ($nSeitenTyp == 1 || $nSeitenTyp == 2)}
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
                {assign var='hasItems' value=false}
                {if isset($category->bUnterKategorien) && $category->bUnterKategorien && (($i+1) < $limit)}
                    {assign var='hasItems' value=true}
                {/if}
                {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                    {assign var='activeParent' value=$activeParents[$i]}
                {/if}
                {if $hasItems}
                    <li class="nav-item {if $hasItems}dropdown{/if} {if $category->kKategorie == $activeId
                        || ((isset($activeParent)
                                && isset($activeParent->kKategorie))
                            && $activeParent->kKategorie == $category->kKategorie)}active{/if}">
                    {*{navitem class="{if $hasItems}dropdown{/if} {if $category->kKategorie == $activeId
                            || ((isset($activeParent)
                                && isset($activeParent->kKategorie))
                            && $activeParent->kKategorie == $category->kKategorie)}active{/if}"
                        href="{$category->cURLFull}"
                        router-data=["toggle"=>"collapse","target"=>"#category_box_{$category->kKategorie}_{$i}"]
                    }*}
                        <a class="nav-link py-3" target="_self" href="{$category->cURLFull}" data-toggle="collapse"
                           data-target="#category_box_{$category->kKategorie}_{$i}"
                           aria-expanded="{if $category->kKategorie == $activeId
                           || ((isset($activeParent)
                           && isset($activeParent->kKategorie))
                           && $activeParent->kKategorie == $category->kKategorie)}true{else}false{/if}">
                            <i class="fa fa-chevron-down float-right pointer mx-2"></i>
                            {$category->cKurzbezeichnung}
                        </a>
                        {nav vertical=true class="collapse {if $category->kKategorie == $activeId
                            || ((isset($activeParent)
                            && isset($activeParent->kKategorie))
                            && $activeParent->kKategorie == $category->kKategorie)}show{/if}" id="category_box_{$category->kKategorie}_{$i}"
                        }
                            {if !empty($category->Unterkategorien)}
                                {include file='snippets/categories_recursive.tpl' i=$i+1 categories=$category->Unterkategorien limit=$limit activeId=$activeId activeParents=$activeParents}
                            {else}
                                {include file='snippets/categories_recursive.tpl' i=$i+1 categoryId=$category->kKategorie limit=$limit categories=null activeId=$activeId activeParents=$activeParents}
                            {/if}
                        {/nav}
                    {*{/navitem}*}
                    </li>
                {else}
                    {navitem class="{if $category->kKategorie == $activeId
                            || ((isset($activeParent)
                                && isset($activeParent->kKategorie))
                            && $activeParent->kKategorie == $category->kKategorie)}active{/if}"
                        href="{$category->cURLFull}"
                    }
                        {$category->cKurzbezeichnung}
                    {/navitem}
                {/if}
            {/foreach}
        {/if}
    {/strip}
{/if}
