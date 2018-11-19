<nav class="backend-sidebar">
    <div class="backend-brandbar">
        <a class="backend-brand" href="index.php" title="Dashboard">
            <img src="{$currentTemplateDir}gfx/JTL-Shop-Logo-rgb.png" alt="JTL-Shop">
        </a>
        <button type="button" class="backend-sidebar-toggle"
                onclick="return toggleSidebar()">
            <i class="fa fa-2x"></i>
        </button>
    </div>
    <div class="backend-navigation">
        <ul class="backend-menu toplevel">
            {foreach $oLinkOberGruppe_arr as $oLinkOberGruppe}
                {assign var='rootEntryName' value=$oLinkOberGruppe->cName|replace:' ':'-'|replace:'&':''|lower}
                {if $oLinkOberGruppe->oLinkGruppe_arr|@count === 0 && $oLinkOberGruppe->oLink_arr|@count === 1}
                    <li class="single {if isset($oLinkOberGruppe->class)}{$oLinkOberGruppe->class}{/if}
                               {if $oLinkOberGruppe->key === $currentMenuPath[0]}current{/if}">
                        <div class="backend-root-label">
                            <a href="{$oLinkOberGruppe->oLink_arr[0]->cURL}" class="parent">
                                <i class="fa fa-2x fa-fw backend-root-menu-icon-{$rootEntryName}"></i>
                                <span>{$oLinkOberGruppe->oLink_arr[0]->cLinkname}</span>
                            </a>
                        </div>
                    </li>
                {else}
                    {assign var='rootEntryName'
                            value=$oLinkOberGruppe->cName|replace:' ':'-'|replace:'&':''|lower}
                    <li id="root-menu-entry-{$rootEntryName}"
                        class="{if isset($oLinkOberGruppe->class)}{$oLinkOberGruppe->class}{/if}
                               {if $oLinkOberGruppe->key === $currentMenuPath[0]}current{/if}">
                        <div class="backend-root-label">
                            <a href="#" class="parent"
                               onclick="return expandRootItem($('#root-menu-entry-{$rootEntryName}'))">
                                <i class="fa fa-2x fa-fw backend-root-menu-icon-{$oLinkOberGruppe->key}"></i>
                                <span>{$oLinkOberGruppe->cName}</span>
                            </a>
                        </div>
                        <ul class="backend-menu secondlevel" id="group-{$rootEntryName}">
                            {foreach $oLinkOberGruppe->oLinkGruppe_arr as $oLinkGruppe}
                                {if $oLinkGruppe->oLink_arr|@count > 0}
                                    {assign var='entryName'
                                            value=$oLinkGruppe->cName|replace:' ':'-'|replace:'&':''|lower}
                                    <li id="dropdown-header-{$entryName}"
                                        class="backend-dropdown-header
                                               {if $oLinkGruppe->key === $currentMenuPath[1]}expanded current{/if}">
                                        <a href="#" onclick="return expandItem($('#dropdown-header-{$entryName}'))">
                                            <span>{$oLinkGruppe->cName}</span>
                                            <i class="fa"></i>
                                        </a>
                                        <ul class="backend-menu thirdlevel" id="collapse-{$entryName}">
                                            {foreach $oLinkGruppe->oLink_arr as $oLink}
                                                <li class="{if !$oLink->cRecht|permission}noperm{/if}
                                                       {if $oLink->key === $currentMenuPath[2]}current{/if}">
                                                    <a href="{$oLink->cURL}">{$oLink->cLinkname}</a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                {/if}
                            {/foreach}
                            {foreach $oLinkOberGruppe->oLink_arr as $oLink}
                                <li class="{if !$oLink->cRecht|permission}noperm{/if}
                                           {if $oLink->key === $currentMenuPath[1]}current{/if}">
                                    <a href="{$oLink->cURL}" class="">{$oLink->cLinkname}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </li>
                {/if}
            {/foreach}
        </ul>
        <script>
            function toggleSidebar()
            {
                var sidebar = $('.backend-sidebar');
                var width, endWidth;

                if(sidebar.hasClass('collapsed')) {
                    sidebar.removeClass('collapsed');
                    sidebar.css('width', 'auto');
                    width = sidebar.width();
                    sidebar.addClass('collapsed');
                    sidebar.css({ width: '' });
                    sidebar.animate({ width: width }, 200, 'swing', function() {
                        sidebar.css({ width: '' });
                        sidebar.removeClass('collapsed');
                    });
                } else {
                    width = sidebar.width();
                    sidebar.addClass('collapsed');
                    endWidth = sidebar.width();
                    sidebar.removeClass('collapsed');
                    sidebar.css({ width: width + 'px' });
                    sidebar.animate({ width: endWidth }, 200, 'swing', function() {
                        sidebar.css({ width: '' });
                    });
                    sidebar.addClass('collapsed');
                }

                collapseAll();
                collapseAllRoot();

                return false;
            }

            function collapseAll()
            {
                $('.expanded').each(function(i, elm) { expandItem($(elm)); });
            }

            function expandItem(li)
            {
                var height, ul = li.find('> ul');

                if(li.hasClass('expanded')) {
                    height = ul.height();
                    ul.css({ height: height + 'px' });
                    ul.animate({ height: 0 }, 400, 'swing', function() { ul.css({ height: '' }); });
                    li.removeClass('expanded');
                } else {
                    collapseAll();
                    ul.css('height', 'auto');
                    height = ul.height();
                    ul.css({ height: '' });
                    ul.animate({ height: height }, 400, 'swing', function() { ul.css({ height: '' }); });
                    li.addClass('expanded');
                }

                return false;
            }

            function collapseAllRoot()
            {
                collapseAll();
                $('.compact-expanded').each(function(i, elm) { expandRootItem($(elm)); });
            }

            function expandRootItem(li)
            {
                if($('.backend-sidebar').hasClass('collapsed') === false) {
                    return false;
                }

                var height, ul = li.find('> ul');

                if(li.hasClass('compact-expanded')) {
                    height = ul.height();
                    ul.css({ height: height + 'px' });
                    ul.animate({ height: 0 }, 200, 'swing', function() {
                        ul.css({ height: '' });
                        li.removeClass('compact-expanded');
                    });
                } else {
                    collapseAllRoot();
                    ul.css('height', 'auto');
                    height = ul.height();
                    ul.css({ height: '0px' });
                    ul.animate({ height: height }, 400, 'swing', function() { ul.css({ height: '' }); });
                    li.addClass('compact-expanded');
                }

                return false;
            }
        </script>
    </div>
</nav>