{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-categories-recursive'}
    {if (!empty($categories) ||isset($categoryId)) && (!isset($i) || isset($i) && isset($limit) && $i < $limit)}
        {strip}
            {if !isset($i)}
                {assign var=i value=0}
            {/if}
            {if !isset($limit)}
                {assign var=limit value=3}
            {/if}
            {if !isset($activeId)}
                {assign var=activeId value=0}
                {if $NaviFilter->hasCategory()}
                    {assign var=activeId value=$NaviFilter->getCategory()->getValue()}
                {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($Artikel)}
                    {assign var=activeId value=$Artikel->gibKategorie()}
                {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($smarty.session.LetzteKategorie)}
                    {assign var=activeId value=$smarty.session.LetzteKategorie}
                {/if}
            {/if}
            {if !isset($activeParents) && ($nSeitenTyp === $smarty.const.PAGE_ARTIKEL || $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
                {get_category_parents categoryId=$activeId assign='activeParents'}
            {/if}
            {if !isset($activeParents)}
                {assign var=activeParents value=null}
            {/if}
            {if empty($categories)}
                {if !isset($categoryBoxNumber)}
                    {assign var=categoryBoxNumber value=null}
                {/if}
                {get_category_array categoryId=$categoryId categoryBoxNumber=$categoryBoxNumber assign='categories'}
            {/if}
            {if !empty($categories)}
                {block name='snippets-categories-recursive-categories'}
                    {foreach $categories as $category}
                        {assign var=hasItems value=false}
                        {if $category->hasChildren() && (($i+1) < $limit)}
                            {assign var=hasItems value=true}
                        {/if}
                        {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                            {assign var=activeParent value=$activeParents[$i]}
                        {/if}
                        {if $hasItems}
                            {block name='snippets-categories-recursive-categories-has-items'}
                                <li class="nav-item {if $hasItems}dropdown{/if} {if $category->getID() == $activeId
                                    || ((isset($activeParent)
                                            && isset($activeParent->kKategorie))
                                        && $activeParent->kKategorie == $category->getID())}active{/if}">
                                {*{navitem class="{if $hasItems}dropdown{/if} {if $category->kKategorie == $activeId
                                        || ((isset($activeParent)
                                            && isset($activeParent->kKategorie))
                                        && $activeParent->kKategorie == $category->kKategorie)}active{/if}"
                                    href="{$category->cURLFull}"
                                    router-data=["toggle"=>"collapse","target"=>"#category_box_{$category->kKategorie}_{$i}"]
                                }*}
                                    <a class="nav-link py-3 px-2 dropdown-toggle" target="_self" href="{$category->getURL()}" data-toggle="collapse"
                                       data-target="#category_box_{$category->getID()}_{$i}"
                                       aria-expanded="{if $category->getID() == $activeId
                                       || ((isset($activeParent)
                                       && isset($activeParent->kKategorie))
                                       && $activeParent->kKategorie == $category->getID())}true{else}false{/if}">
                                        {$category->getShortName()}
                                    </a>
                                    {nav vertical=true class="collapse {if $category->getID() == $activeId
                                        || ((isset($activeParent)
                                        && isset($activeParent->kKategorie))
                                        && $activeParent->kKategorie == $category->getID())}show{/if}" id="category_box_{$category->getID()}_{$i}"
                                    }
                                        {block name='snippets-categories-recursive-include-categories-recursive'}
                                            {if $category->hasChildren()}
                                                {include file='snippets/categories_recursive.tpl' i=$i+1 categories=$category->getChildren() limit=$limit activeId=$activeId activeParents=$activeParents}
                                            {else}
                                                {include file='snippets/categories_recursive.tpl' i=$i+1 categoryId=$category->getID() limit=$limit categories=null activeId=$activeId activeParents=$activeParents}
                                            {/if}
                                        {/block}
                                    {/nav}
                                {*{/navitem}*}
                                </li>
                            {/block}
                        {else}
                            {block name='snippets-categories-recursive-has-not-items'}
                                {navitem class="{if $category->getID() == $activeId
                                        || ((isset($activeParent)
                                            && isset($activeParent->kKategorie))
                                        && $activeParent->kKategorie == $category->getID())} active{/if}"
                                    href=$category->getURL()
                                    router-class="px-2"
                                }
                                    {$category->getShortName()}
                                {/navitem}
                            {/block}
                        {/if}
                    {/foreach}
                {/block}
            {/if}
        {/strip}
    {/if}
{/block}
