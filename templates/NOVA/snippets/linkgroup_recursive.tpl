{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-linkgroup-recursive'}
    {if isset($linkgroupIdentifier) && (!isset($i) || isset($limit) && $i < $limit)}
        {strip}
            {if !isset($i)}
                {assign var=i value=0}
            {/if}
            {if !isset($limit)}
                {assign var=limit value=3}
            {/if}
            {if !isset($activeId)}
                {assign var=activeId value=0}
                {if isset($Link) && $Link->getID() > 0}
                    {assign var=activeId value=$Link->getID()}
                {elseif Shop::$kLink > 0}
                    {assign var=activeId value=JTL\Shop::$kLink}
                    {assign var=Link value=JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
                {/if}
            {/if}
            {if !isset($activeParents)}
                {assign var=activeParents value=JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
            {/if}
            {if !isset($links)}
                {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
            {/if}
            {if !empty($links)}
                {block name='snippets-linkgroup-recursive-list'}
                    {foreach $links as $li}
                        {assign var=hasItems value=$li->getChildLinks()->count() > 0 && (($i+1) < $limit)}
                        {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                            {assign var=activeParent value=$activeParents[$i]}
                        {/if}
                        <li class="nav-item {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}dropdown dropdown-multi{/if}{if $li->getIsActive() || (isset($activeParent) && $activeParent == $li->getID())} active{/if}">
                            {block name='snippets-linkgroup-recursive-link'}
                                <a href="{$li->getURL()}"{if $li->getNoFollow()}
                                    rel="nofollow"{/if}
                                    {if !empty($li->getTitle())}title="{$li->getTitle()}"{/if}
                                    class="nav-link {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}nav-sub{/if}"
                                    data-ref="{$li->getID()}">
                                    {$li->getName()}
                                    {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}<i class="fa fa-caret-down nav-toggle pull-right"></i>{/if}
                                </a>
                            {/block}
                            {if $hasItems}
                                {block name='snippets-linkgroup-recursive-include-linkgroup-recursive'}
                                    <ul class="nav">
                                        {if $li->getChildLinks()->count() > 0}
                                            {include file='snippets/linkgroup_recursive.tpl' i=$i+1 links=$li->getChildLinks() limit=$limit activeId=$activeId activeParents=$activeParents}
                                        {else}
                                            {include file='snippets/linkgroup_recursive.tpl' i=$i+1 links=array($li) limit=$limit activeId=$activeId activeParents=$activeParents}
                                        {/if}
                                    </ul>
                                {/block}
                            {/if}
                        </li>
                    {/foreach}
                {/block}
            {/if}
        {/strip}
    {/if}
{/block}
