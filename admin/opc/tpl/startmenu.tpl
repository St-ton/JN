{if \JTL\Shop::isAdmin() && $opc->isEditMode() === false && $opc->isPreviewMode() === false}
    {$opcStartUrl       = "{$ShopURL}/admin/opc.php"}
    {$curPageUrl        = $opcPageService->getCurPageUri()}
    {$curPageId         = $opcPageService->createCurrentPageId()}
    {$publicDraft       = $opcPageService->getPublicPage($curPageId)}
    {if $publicDraft === null}
        {$publicDraftKey = 0}
    {else}
        {$publicDraftKey = $publicDraft->getKey()}
    {/if}
    {$pageDrafts        = $opcPageService->getDrafts($curPageId)}
    {$adminSessionToken = $opc->getAdminSessionToken()}
    {$languages         = $smarty.session.Sprachen}
    {$currentLanguage   = $smarty.session.currentLanguage}

    <script>
        let languages = [
            {foreach $languages as $lang}
                {
                    id:      {$lang->id},
                    nameDE:  '{$lang->nameDE}',
                    pageId:  '{$opcPageService->createCurrentPageId($lang->id)}}',
                    pageUri: '{$opcPageService->getCurPageUri($lang->id)}}',
                },
            {/foreach}
        ];

        let currentLanguage = {
            id: {$currentLanguage->id},
        };

        function openOpcStartMenu()
        {
            $('#opcSidebar').addClass('opc-open');
            $('#opc-startmenu').addClass('opc-close');
            $('#opc-page-wrapper').addClass('opc-shifted');
        }

        function closeOpcStartMenu()
        {
            $('#opcSidebar').removeClass('opc-open');
            $('#opc-startmenu').removeClass('opc-close');
            $('#opc-page-wrapper').removeClass('opc-shifted');
        }

        function deleteOpcDraft(draftKey)
        {
            if (confirm("Wollen Sie diesen Entwurf wirklich löschen?")) {
                $.ajax({
                    method: 'post',
                    url: '{$opcStartUrl}',
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

        function getSelectedOpcDraftkeys()
        {
            return $('.draft-checkbox')
                .filter(':checked')
                .closest('.opc-draft')
                .map((i,elm) => $(elm).data('draft-key'))
                .get();
        }

        function deleteSelectedOpcDrafts()
        {
            let draftKeys = getSelectedOpcDraftkeys();

            if (confirm(draftKeys.length + " Entwürfe werden gelöscht! Fortfahren?")) {
                $.ajax({
                    method: 'post',
                    url: '{$opcStartUrl}',
                    data: {
                        action: 'discard-bulk',
                        draftKeys: draftKeys,
                        jtl_token: '{$adminSessionToken}'
                    },
                    success: function(jqxhr) {
                        if (jqxhr === 'ok') {
                            draftKeys.forEach(draftKey => {
                                let draftItem = $('#opc-draft-' + draftKey);
                                draftItem.animate({ opacity: 'toggle' }, 500, () => draftItem.remove());
                                window.localStorage.removeItem('opcpage.' + draftKey);
                            });
                        }
                    }
                });
            }
        }

        function filterOpcDrafts()
        {
            let searchTerm = $('#opc-filter-search').val().toLowerCase();

            $('#opc-draft-list').children().each((i, item) => {
                item = $(item);
                item.find('.draft-checkbox').prop('checked', false);

                let draftName = item.find('.opc-draft-name')[0].innerText.toLowerCase();

                if (draftName.indexOf(searchTerm) === -1) {
                    item.hide();
                    item.find('.draft-checkbox').removeClass('filtered-draft');
                } else {
                    item.show();
                    item.find('.draft-checkbox').addClass('filtered-draft');
                }
            });
            opcDraftCheckboxChanged();
            $('#check-all-drafts').prop('checked', false);
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
            opcDraftCheckboxChanged();
        }

        function duplicateOpcDraft(draftKey)
        {
            $.ajax({
                method: 'post',
                url: '{$opcStartUrl}',
                data: {
                    action: 'duplicate-bulk',
                    draftKeys: [draftKey],
                    jtl_token: '{$adminSessionToken}'
                },
                success: function(jqxhr) {
                    if (jqxhr === 'ok') {
                        $.evo.io().call(
                            'getOpcDraftsHtml',
                            ['{$curPageId}', '{$adminSessionToken}', languages, currentLanguage],
                            { },
                            () => {
                                opcDraftCheckboxChanged();
                            }
                        );
                    }
                }
            });
        }

        function duplicateSelectedOpcDrafts()
        {
            let draftKeys = getSelectedOpcDraftkeys();

            $.ajax({
                method: 'post',
                url: '{$opcStartUrl}',
                data: {
                    action: 'duplicate-bulk',
                    draftKeys: draftKeys,
                    jtl_token: '{$adminSessionToken}'
                },
                success: function(jqxhr) {
                    if (jqxhr === 'ok') {
                        $.evo.io().call(
                            'getOpcDraftsHtml',
                            ['{$curPageId}', '{$adminSessionToken}', languages, currentLanguage],
                            { },
                            () => {
                                opcDraftCheckboxChanged();
                            }
                        );
                    }
                }
            });
        }

        function opcDraftCheckboxChanged()
        {
            let draftKeys = getSelectedOpcDraftkeys();
            $('#opc-bulk-actions').attr('disabled', draftKeys.length === 0);
        }
    </script>
    <div id="opc">
        {if $pageDrafts|count === 0}
            <nav id="opc-startmenu">
                <form method="post" action="{$opcStartUrl}">
                    <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                    <input type="hidden" name="pageId" value="{$curPageId}">
                    <input type="hidden" name="pageUrl" value="{$curPageUrl}">
                    <button type="submit" name="action" value="extend" class="opc-btn-primary">
                        <img src="{$ShopURL}/admin/opc/gfx/icon-OPC.svg" alt="OPC Start Icon" id="opc-start-icon">
                        <span id="opc-start-label">OnPage Composer</span>
                    </button>
                </form>
            </nav>
        {else}
            <nav id="opc-startmenu">
                <button type="button" class="opc-btn-primary" onclick="openOpcStartMenu()">
                    <img src="{$ShopURL}/admin/opc/gfx/icon-OPC.svg" alt="OPC Start Icon" id="opc-start-icon">
                    <span id="opc-start-label">OnPage Composer</span>
                </button>
            </nav>
            <div id="opcSidebar">
                <header id="opcHeader">
                    <button id="opcMenuBtn" class="opc-header-btn">
                        <i class="fa fas fa-ellipsis-v"></i>
                    </button>
                    <h1 id="opc-sidebar-title">
                        Seite bearbeiten
                    </h1>
                    <button onclick="closeOpcStartMenu()" class="opc-float-right opc-header-btn">
                        <i class="fa fas fa-times"></i>
                    </button>
                </header>
                <div id="opc-sidebar-tools">
                    <h2 id="opc-sidebar-second-title">Alle Entwürfe</h2>
                    <div class="opc-group">
                        <input type="search" class="opc-filter-control float-left" placeholder="&#xF002; Suche"
                               oninput="filterOpcDrafts()" id="opc-filter-search">
                        <div class="opc-filter-control opc-dropdown float-left" id="opc-filter-status">
                            <button class="opc-dropdown-btn" data-toggle="dropdown">
                                Status
                            </button>
                            <div class="dropdown-menu opc-dropdown-menu">
                                <button class="opc-dropdown-item" onclick="orderOpcDraftsBy(0);return false">Status</button>
                                <button class="opc-dropdown-item" onclick="orderOpcDraftsBy(1);return false">Name</button>
                            </div>
                        </div>
                    </div>
                    <input type="checkbox" id="check-all-drafts" onchange="checkAllOpcDrafts()">
                    <label for="check-all-drafts" class="opc-check-all">
                        Alle an-/abwählen
                    </label>
                    <div class="opc-dropdown" id="opc-bulk-actions-dropdown">
                        <button type="button" id="opc-bulk-actions" data-toggle="dropdown" disabled>
                            <span id="opc-bulk-actions-label">
                                Aktionen
                            </span>
                            <i class="fa fas fa-fw fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu opc-dropdown-menu" id="opc-bulk-dropdown">
                            <a href="#" onclick="duplicateSelectedOpcDrafts();return false" class="opc-dropdown-item">
                                Duplizieren
                            </a>
                            <a href="#" onclick="deleteSelectedOpcDrafts();return false" class="opc-dropdown-item">
                                Löschen
                            </a>
                        </div>
                    </div>
                </div>
                <div id="opc-sidebar-content">
                    <ul id="opc-draft-list">
                        {include file=$opcDir|cat:'tpl/draftlist.tpl'}
                    </ul>
                </div>
                <div id="opc-sidebar-footer">
                    <form method="post" action="{$opcStartUrl}">
                        <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                        <input type="hidden" name="pageId" value="{$curPageId}">
                        <input type="hidden" name="pageUrl" value="{$curPageUrl}">
                        <button type="submit" name="action" value="extend" class="opc-btn-primary opc-full-width">
                            Neuer Entwurf
                        </button>
                    </form>
                </div>
            </div>
        {/if}
    </div>
{/if}