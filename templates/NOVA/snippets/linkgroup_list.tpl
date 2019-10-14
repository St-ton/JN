{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='snippets-linkgroup-list'}
    {if isset($linkgroupIdentifier)}
    {strip}
    {assign var=checkLinkParents value=false}
        {if isset($Link) && $Link->getID() > 0}
            {assign var=activeId value=$Link->getID()}
        {elseif Shop::$kLink > 0}
            {assign var=activeId value=JTL\Shop::$kLink}
            {assign var=Link value=JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
        {/if}
        {if !isset($activeParents) && (isset($Link))}
            {assign var=activeParents value=JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
            {assign var=checkLinkParents value=true}
        {/if}
        {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
        {if !empty($links)}
            {block name='snippets-linkgroup-list-links'}
                {foreach $links as $li}
                    {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}
                        {block name='snippets-linkgroup-list-links-dropdown'}
                            {navitemdropdown text=$li->getName() class="{if $activeId == $li->getId()}active{/if}"}
                                {container}
                                    {link href=$li->getURL() title=$li->getName()}
                                        {$li->getName()}
                                    {/link}
                                    <hr class="hr-sm d-none d-md-block">
                                    {row}
                                    {foreach $li->getChildLinks() as $subli}
                                        {col cols=12 md=6 lg=3}
                                            {if !empty($subli->getName())}
                                                {dropdownitem tag="div" active=($subli->getIsActive() || ($checkLinkParents === true && isset($activeParents) && in_array($subli->getID(), $activeParents)))}
                                                    <div class="title mt-3">
                                                        {link href=$subli->getURL() rel="{if $subli->getNoFollow()}nofollow{/if}"}
                                                            {$subli->getName()}
                                                        {/link}
                                                    </div>
                                                {/dropdownitem}
                                            {/if}
                                        {/col}
                                    {/foreach}
                                    {/row}
                                {/container}
                            {/navitemdropdown}
                        {/block}
                    {else}
                        {block name='snippets-linkgroup-list-links-navitem'}
                            {navitem href=$li->getURL() nofollow=$li->getNoFollow() class="nav-scrollbar-item {if $activeId == $li->getId()}active{/if}{if $tplscope=='sitemap'} py-2{/if}" router-class="{if $tplscope=='sitemap'}nice-deco{/if}"}
                                {$li->getName()}
                            {/navitem}
                        {/block}
                    {/if}
                {/foreach}
            {/block}
        {/if}
    {/strip}
    {/if}
{/block}
