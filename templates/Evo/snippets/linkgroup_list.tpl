{if isset($linkgroupIdentifier)}
{strip}
    {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
    {assign var=checkLinkParents value=false}
    {if !empty($links)}
        {if isset($Link->kLink) && (int)$Link->kLink > 0}
            {assign var='activeId' value=$Link->kLink}
        {elseif Shop::$kLink > 0}
            {assign var='activeId' value=Shop::$kLink}
            {assign var='Link' value=LinkHelper::getInstance()->getPageLink($activeId)}
        {/if}
        {if !isset($activeParents) && (isset($Link))}
            {assign var='activeParents' value=LinkHelper::getInstance()->getParentsArray($activeId)}
            {assign var=checkLinkParents value=true}
        {/if}
        {foreach $links as $li}
            <li class="{if !empty($li->oSub_arr) && isset($dropdownSupport)}dropdown dropdown-multi{/if}{if $li->bIsActive || ($checkLinkParents === true && isset($activeParents) && in_array($li->kLink, $activeParents))} active{/if}{if $tplscope === 'megamenu' && !empty($li->oSub_arr)} bs-hover-enabled{/if}">
                {if isset($li->cLocalizedName[$smarty.session.cISOSprache])}
                    <a href="{$li->cURLFull}"{if $li->cNoFollow === 'Y'} rel="nofollow"{/if}{if !empty($li->cTitle)} title="{$li->cTitle}"{/if}{if !empty($li->oSub_arr) && isset($dropdownSupport)} class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-hover-delay="100" data-delay="300"{/if}>
                        {$li->cLocalizedName|trans}
                        {if !empty($li->oSub_arr) && isset($dropdownSupport)} <span class="{if !empty($caret)}{$caret}{else}caret{/if}"></span>{/if}
                    </a>
                    {if !empty($li->oSub_arr)}
                        <ul class="{if isset($dropdownSupport)}{if $tplscope !== 'megamenu'}inline {/if}dropdown-menu keepopen{else}submenu list-unstyled{/if}">
                            {foreach name='subs' from=$li->oSub_arr item='subli'}
                                {if !empty($subli->cLocalizedName)}
                                <li{if $subli->bIsActive || ($checkLinkParents === true && isset($activeParents) && in_array($subli->kLink, $activeParents))} class="active"{/if}>
                                    <a href="{$subli->cURLFull}"{if $subli->cNoFollow === 'Y'} rel="nofollow"{/if}{if !empty($subli->cTitle)} title="{$subli->cTitle}"{/if}>
                                        {$subli->cLocalizedName|trans}
                                    </a>
                                </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/if}
                {/if}
            </li>
        {/foreach}
    {/if}
{/strip}
    {*{$linkGroup = $newLinkGroups->getLinkgroupByTemplate($linkgroupIdentifier)}*}
    {get_navigation2 linkgroupIdentifier=$linkgroupIdentifier assign='links'}
    {foreach $links as $li}
        <li class="{if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}dropdown dropdown-multi{/if}{if $li->getIsActive() || ($checkLinkParents === true && isset($activeParents) && in_array($li->getID(), $activeParents))} active{/if}{if $tplscope === 'megamenu' && $li->getChildLinks()->count() > 0} bs-hover-enabled{/if}">
            <a href="{$li->getURL()}"{if $li->getNoFollow()} rel="nofollow"{/if}{if !empty($li->getTitle())} title="{$li->getTitle()}"{/if}{if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)} class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-hover-delay="100" data-delay="300"{/if}>
                {$li->getName()}
                {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)} <span class="{if !empty($caret)}{$caret}{else}caret{/if}"></span>{/if}
            </a>
            {if $li->getChildLinks()->count() > 0}
                <ul class="{if isset($dropdownSupport)}{if $tplscope !== 'megamenu'}inline {/if}dropdown-menu keepopen{else}submenu list-unstyled{/if}">
                    {foreach name='subs' from=$li->getChildLinks() item='subli'}
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