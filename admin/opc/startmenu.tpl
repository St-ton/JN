{if \JTL\Shop::isAdmin() && $opc->isEditMode() === false}
    {$curPage           = $opcPageService->getCurPage()}
    {$curPageId         = $curPage->getId()}
    {$pageDrafts        = $opcPageService->getDrafts($curPageId)}
    {$adminSessionToken = $opc->getAdminSessionToken()}

    <script>
        function openOpcStartMenu()
        {
            $('#opc-sidebar').addClass('opc-open');
            $('#opc-startmenu').addClass('opc-close');
            $('#opc-page-wrapper').addClass('opc-shifted');
        }

        function closeOpcStartMenu()
        {
            $('#opc-sidebar').removeClass('opc-open');
            $('#opc-startmenu').removeClass('opc-close');
            $('#opc-page-wrapper').removeClass('opc-shifted');
        }

        function deleteOpcDraft(draftKey)
        {
            if (confirm("Wollen Sie diesen Entwurf wirklich löschen?")) {
                $.ajax({
                    method: 'post',
                    url: '{$ShopURL}/admin/onpage-composer.php',
                    data: {
                        action: 'discard',
                        pageKey: draftKey,
                        jtl_token: '{$adminSessionToken}'
                    },
                    success: function(jqxhr) {
                        if (jqxhr === 'ok') {
                            let draftItem = $('#opc-draft-' + draftKey);
                            draftItem.animate({ opacity: 'toggle' }, 500, () => draftItem.remove());
                            window.localStorage.removeItem('opcpage.' + draftKey);
                        }
                    }
                });
            }
        }

        function deleteSelectedOpcDrafts()
        {
            // TODO
            if (confirm("Wollen Sie die gewählten Entwürfe wirklich löschen?")) {

            }
        }

        function filterOpcDrafts()
        {
            let searchTerm = $('#opc-filter-search').val();

            $('#opc-draft-list').children().each((i, item) => {
                item = $(item);
                item.find('.draft-checkbox').prop('checked', false);

                let draftName = item.find('.opc-draft-name')[0].innerText;

                if (draftName.indexOf(searchTerm) === -1) {
                    item.hide();
                    item.find('.draft-checkbox').removeClass('filtered-draft');
                } else {
                    item.show();
                    item.find('.draft-checkbox').addClass('filtered-draft');
                }
            });
        }

        function orderOpcDraftsBy(criteria)
        {
            let draftsList  = $('#opc-draft-list');
            let draftsArray = draftsList.children().toArray();

            if (criteria === 0) {
                $('#opc-filter-status .opc-dropdown-btn').text('Status');
                draftsArray.sort((a, b) => a.dataset.draftStatus - b.dataset.draftStatus);
            } else if(criteria === 1) {
                $('#opc-filter-status .opc-dropdown-btn').text('Name');
                draftsArray.sort((a, b) => {
                    if (a.dataset.draftName < b.dataset.draftName) {
                        return -1;
                    }
                    if (a.dataset.draftName > b.dataset.draftName) {
                        return +1;
                    }
                    return 0;
                });
            }
            draftsArray.forEach(draft => draftsList.append(draft));

        }

        function checkAllOpcDrafts()
        {
            $('.draft-checkbox.filtered-draft').prop(
                'checked',
                $('#check-all-drafts').prop('checked')
            );
        }
    </script>
    <div id="opc">
        {if $pageDrafts|count === 0}
            <nav id="opc-startmenu">
                <form method="post" action="{$ShopURL}/admin/onpage-composer.php">
                    <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                    <input type="hidden" name="pageId" value="{$curPage->getId()}">
                    <input type="hidden" name="pageUrl" value="{$curPage->getUrl()}">
                    <button type="submit" name="action" value="extend" class="opc-btn-primary">
                        <img src="{$ShopURL}/admin/opc/icon-OPC.svg" alt="OPC Start Icon" id="opc-start-icon">
                        <span id="opc-start-label">OnPage Composer</span>
                    </button>
                </form>
            </nav>
        {else}
            <nav id="opc-startmenu">
                <button class="opc-btn-primary" onclick="openOpcStartMenu()">
                    <img src="{$ShopURL}/admin/opc/icon-OPC.svg" alt="OPC Start Icon" id="opc-start-icon">
                    <span id="opc-start-label">OnPage Composer</span>
                </button>
            </nav>
            <div id="opc-sidebar" class="opc-open">
                <header id="opc-header">
                    {*<button>*}
                        {*<i class="fa fas fa-ellipsis-v"></i>*}
                    {*</button>*}
                    <h1 id="opc-sidebar-title">
                        Seite bearbeiten
                    </h1>
                    <button onclick="closeOpcStartMenu()" class="float-right">
                        <i class="fa fas fa-times"></i>
                    </button>
                </header>
                <div id="opc-sidebar-tools">
                    <h2 id="opc-sidebar-second-title">Alle Entwürfe</h2>
                    <div class="opc-group">
                        <input type="text" class="opc-filter-control" placeholder="&#xF002; Suche"
                               oninput="filterOpcDrafts()" id="opc-filter-search">
                        <div class="opc-filter-control opc-dropdown" id="opc-filter-status">
                            <button class="opc-dropdown-btn" data-toggle="dropdown">
                                Status
                            </button>
                            <div class="dropdown-menu opc-dropdown-menu">
                                <a href="#" onclick="orderOpcDraftsBy(0);return false">Status</a>
                                <a href="#" onclick="orderOpcDraftsBy(1);return false">Name</a>
                            </div>
                        </div>
                    </div>
                    <input type="checkbox" id="check-all-drafts" onchange="checkAllOpcDrafts()">
                    <label for="check-all-drafts" class="opc-check-all">
                        Alle an-/abwählen
                    </label>
                    <div class="opc-dropdown" style="float: right">
                        <button type="button" id="opc-bulk-actions" data-toggle="dropdown">
                            <span id="opc-bulk-actions-label">Bulk Actions</span>
                            <i class="fa fas fa-fw fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu opc-dropdown-menu" id="opc-bulk-dropdown">
                            {*<a href="#" onclick="">Duplizieren</a>*}
                            <a href="#" onclick="deleteSelectedOpcDrafts()">Löschen</a>
                        </div>
                    </div>
                </div>
                <div id="opc-sidebar-content">
                    <ul id="opc-draft-list">
                        {foreach $pageDrafts as $i => $draft}
                            {$draftStatus = $draft->getStatus()}
                            <li class="opc-draft" id="opc-draft-{$draft->getKey()}" data-draft-status="{$draftStatus}"
                                data-draft-name="{$draft->getName()}">
                                <form method="post" action="{$ShopURL}/admin/onpage-composer.php">
                                    <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                                    <input type="hidden" name="pageKey" value="{$draft->getKey()}">
                                    <input type="checkbox" id="check-{$draft->getKey()}"
                                           class="draft-checkbox filtered-draft">
                                    <label for="check-{$draft->getKey()}" class="opc-draft-name">
                                        {$draft->getName()}
                                    </label>
                                    {if $draftStatus === 0}
                                        <span class="opc-draft-status opc-public">
                                            <i class="fa fas fa-circle fa-xs"></i> ÖFFENTLICH
                                        </span>
                                    {elseif $draftStatus === 1}
                                        <span class="opc-draft-status opc-planned">
                                            <i class="fa fas fa-circle fa-xs"></i> GEPLANT
                                        </span>
                                    {elseif $draftStatus === 2}
                                        <span class="opc-draft-status opc-status-draft">
                                            <i class="fa fas fa-circle fa-xs"></i> ENTWURF
                                        </span>
                                    {elseif $draftStatus === 3}
                                        <span class="opc-draft-status opc-backdate">
                                            <i class="fa fas fa-circle fa-xs"></i> VERGANGEN
                                        </span>
                                    {/if}
                                    <div class="opc-draft-info">
                                        <div class="opc-draft-info-line">
                                            updated
                                        </div>
                                        <div class="opc-draft-info-line">
                                            published
                                        </div>
                                        <div class="opc-draft-actions">
                                            <button type="submit" name="action" value="edit" data-toggle="tooltip"
                                                    title="Bearbeiten" data-placement="bottom" data-container="#opc">
                                                <i class="fa fa-lg fa-fw fas fa-pencil-alt"></i>
                                            </button>
                                            <button data-toggle="tooltip" title="Duplizieren" data-placement="bottom"
                                                    data-container="#opc">
                                                <i class="fa fa-lg fa-fw far fa-clone"></i>
                                            </button>
                                            <button data-toggle="tooltip" title="Für andere Sprache übernehmen"
                                                    data-placement="bottom" data-container="#opc">
                                                <i class="fa fa-lg fa-fw fas fa-language"></i>
                                            </button>
                                            <button type="button" onclick="deleteOpcDraft({$draft->getKey()})"
                                                    data-toggle="tooltip" title="Löschen"
                                                    data-placement="bottom" data-container="#opc">
                                                <i class="fa fa-lg fa-fw fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </li>
                        {/foreach}
                    </ul>
                </div>
                <div id="opc-sidebar-footer">
                    <form method="post" action="admin/onpage-composer.php">
                        <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                        <input type="hidden" name="pageId" value="{$curPage->getId()}">
                        <input type="hidden" name="pageUrl" value="{$curPage->getUrl()}">
                        <button type="submit" name="action" value="extend" class="opc-btn-primary opc-full-width">
                            Neuer Entwurf
                        </button>
                    </form>
                </div>
            </div>
        {/if}
    </div>
    {*<div id="opc-page-wrapper">*}
{/if}