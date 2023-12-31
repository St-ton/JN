<div class="collapse" id="sidebar">
    <div class="row no-gutters align-items-center flex-nowrap topbar px-3">
        <div class="col">
            <a href="{$adminURL}/" title="{__('dashboard')}">
                <img class="brand-logo" width="101" height="32" src="{$templateBaseURL}gfx/JTL-Shop-Logo-rgb.png" alt="JTL-Shop">
                <img class="brand-logo brand-logo-white" width="101" height="32" src="{$templateBaseURL}gfx/JTL-Shop-Logo-rgb-white.png" alt="JTL-Shop">
            </a>
        </div>
        <div class="col-auto ml-auto">
            <button type="button" class="btn btn-link btn-sm text-primary" data-toggle="sidebar-collapse">
                <span class="fal fa-angle-double-left"></span>
            </button>
        </div>
    </div>
    <div class="navigation pb-4">
        <ul class="nav categories">
            {foreach $oLinkOberGruppe_arr as $oLinkOberGruppe}
                {assign var=rootEntryName value=$oLinkOberGruppe->cName|regex_replace:'/[^a-zA-Z0-9]/':'-'|lower}
                {if count($oLinkOberGruppe->oLinkGruppe_arr) === 0 && count($oLinkOberGruppe->oLink_arr) === 1}
                    <li class="nav-item {if isset($oLinkOberGruppe->class)}{$oLinkOberGruppe->class}{/if}
                               {if $oLinkOberGruppe->active === true}active{/if}">
                        <a href="{$oLinkOberGruppe->oLink_arr[0]->cURL}" class="nav-link">
                            <span class="category-icon">
                                <i class="fa fa-2x fa-fw backend-root-menu-icon-{$rootEntryName}"></i>
                            </span>
                            <span class="category-title">{$oLinkOberGruppe->oLink_arr[0]->cLinkname}</span>
                        </a>
                    </li>
                {else}
                    <li id="root-menu-entry-{$rootEntryName}"
                        class="nav-item {if isset($oLinkOberGruppe->class)}{$oLinkOberGruppe->class}{/if}
                               {if $oLinkOberGruppe->active === true}active{/if}
                                {if $oLinkOberGruppe@last} mb-5{/if}">
                        <a href="#" class="nav-link {if $oLinkOberGruppe->active === false} collapsed{/if}">
                            <span class="category-icon">{include file="img/icons/{$oLinkOberGruppe->icon}.svg"}</span>
                            <span class="category-title">{$oLinkOberGruppe->cName}</span>
                        </a>
                        <ul class="nav submenu" id="group-{$rootEntryName}">
                            {foreach $oLinkOberGruppe->oLinkGruppe_arr as $oLinkGruppe}
                                {assign var=entryName value=$oLinkGruppe->cName|replace:' ':'-'|replace:'&':''|lower}
                                {if is_object($oLinkGruppe->oLink_arr)}
                                    <li id="dropdown-header-{$entryName}"
                                        class="nav-item {if $oLinkGruppe->active === true}active current-item{/if}">
                                        <a class="nav-link" href="{$oLinkGruppe->oLink_arr->cURL}"
                                            {if !empty($oLinkGruppe->oLink_arr->target)}
                                                target="{$oLinkGruppe->oLink_arr->target}"{/if}>
                                            {$oLinkGruppe->cName}
                                        </a>
                                    </li>
                                {elseif count($oLinkGruppe->oLink_arr) > 0}
                                    <li id="dropdown-header-{$entryName}"
                                        class="nav-item {if $oLinkGruppe->active === true} active{/if}">
                                        <a class="nav-link {if $oLinkGruppe->active === false}collapsed{/if}"
                                           href="#"
                                           data-toggle="collapse"
                                           data-target="#collapse-{$entryName}"
                                           aria-controls="collapse-{$entryName}"
                                           aria-expanded="{if $oLinkGruppe->active === true}true{else}false{/if}">
                                            <span>{$oLinkGruppe->cName}</span>
                                            <i class="far fa-chevron-down rotate-180"></i>
                                        </a>
                                        <ul class="nav submenu collapse {if $oLinkGruppe->active === true}show{/if}"
                                            id="collapse-{$entryName}"
                                            data-parent="#sidebar">
                                            {foreach $oLinkGruppe->oLink_arr as $oLink}
                                                <li class="nav-item {if $oLink->active === true}active current-item{/if}">
                                                    <a class="nav-link" href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                {/if}
                            {/foreach}
                            {foreach $oLinkOberGruppe->oLink_arr as $oLink}
                                <li class="nav-item {if $oLink->active === true}active{/if}">
                                    <a href="{$oLink->cURL}" class="nav-link">{$oLink->cLinkname}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </li>
                {/if}
            {/foreach}
        </ul>
    </div>
    <div class="opaque-background"></div>
    <script>
        let curItem = $('.current-item');

        if(curItem.length) {
            curItem[0].scrollIntoView();
        }
    </script>
</div>
