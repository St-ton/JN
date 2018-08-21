{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if isset($linkgroupIdentifier)}
{strip}
{assign var=checkLinkParents value=false}
    {if isset($Link) && $Link->getID() > 0}
        {assign var='activeId' value=$Link->getID()}
    {elseif Shop::$kLink > 0}
        {assign var='activeId' value=Shop::$kLink}
        {assign var='Link' value=Shop::Container()->getLinkService()->getLinkByID($activeId)}
    {/if}
    {if !isset($activeParents) && (isset($Link))}
        {assign var='activeParents' value=Shop::Container()->getLinkService()->getParentIDs($activeId)}
        {assign var=checkLinkParents value=true}
    {/if}
    {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
    {if !empty($links)}
        {foreach $links as $li}
            <li class="{if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}dropdown dropdown-multi{/if}{if $li->getIsActive() || ($checkLinkParents === true && isset($activeParents) && in_array($li->getID(), $activeParents))} active{/if}{if $tplscope === 'megamenu' && $li->getChildLinks()->count() > 0} bs-hover-enabled{/if}">
                <a href="{$li->getURL()}"{if $li->getNoFollow()} rel="nofollow"{/if}{if !empty($li->getTitle())} title="{$li->getTitle()}"{/if}{if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)} class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-hover-delay="100" data-delay="300"{/if}>
                    {$li->getName()}
                    {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)} <span class="{if !empty($caret)}{$caret}{else}caret{/if}"></span>{/if}
                </a>
                {if $li->getChildLinks()->count() > 0}
                    <ul class="{if isset($dropdownSupport)}{if $tplscope !== 'megamenu'}inline {/if}dropdown-menu keepopen{else}submenu list-unstyled{/if}">
                        {foreach $li->getChildLinks() as $subli}
                            {if !empty($subli->getName())}
                                <li{if $subli->getIsActive() || ($checkLinkParents === true && isset($activeParents) && in_array($subli->getID(), $activeParents))} class="active"{/if}>
                                    <a href="{$subli->getURL()}"{if $subli->getNoFollow()} rel="nofollow"{/if}{if !empty($subli->getTitle())} title="{$subli->getTitle()}"{/if}>
                                        {$subli->getName()}
                                    </a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}
            </li>
        {/foreach}
    {/if}
{/strip}
{/if}
