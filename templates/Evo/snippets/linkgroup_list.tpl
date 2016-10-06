{if isset($linkgroupIdentifier)}
{strip}
    {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
    {if !empty($links)}
        {if isset($Link) && intval($Link->kLink) > 0}
            {assign var='activeId' value=$Link->kLink}
        {else}
            {assign var='activeId' value=Shop::$kLink}
            {assign var='Link' value=LinkHelper::getInstance()->getPageLink($activeId)}
        {/if}
        {if !isset($activeParents) && (isset($Link))}
            {assign var='activeParents' value=LinkHelper::getInstance()->getParentsArray($activeId)}
        {/if}
        {foreach name='links' from=$links item='li'}
            <li class="{if !empty($li->oSub_arr) && isset($dropdownSupport)}dropdown dropdown-multi{/if}{if $li->bIsActive || (isset($activeParents) && in_array($li->kLink, $activeParents))} active{/if}{if $tplscope === 'megamenu' && !empty($li->oSub_arr)} bs-hover-enabled{/if}">
                {if isset($li->cLocalizedName[$smarty.session.cISOSprache])}
                    <a href="{$li->cURLFull}"{if $li->cNoFollow === 'Y'} rel="nofollow"{/if}{if !empty($li->cTitle)} title="{$li->cTitle}"{/if}{if !empty($li->oSub_arr) && isset($dropdownSupport)} class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-hover-delay="100" data-delay="300"{/if}>
                        {$li->cLocalizedName|trans}
                        {if !empty($li->oSub_arr) && isset($dropdownSupport)} <span class="caret"></span>{/if}
                    </a>
                    {if !empty($li->oSub_arr)}
                        <ul class="{if isset($dropdownSupport)}{if $tplscope !== 'megamenu'}inline {/if}dropdown-menu keepopen{else}submenu list-unstyled{/if}">
                            {foreach name='subs' from=$li->oSub_arr item='subli'}
                                {if !empty($subli->cLocalizedName)}
                                <li{if $subli->bIsActive || (isset($activeParents) && in_array($subli->kLink, $activeParents))} class="active"{/if}>
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
{/if}