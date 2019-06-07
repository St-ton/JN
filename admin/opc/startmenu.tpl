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
        if(confirm("Wollen Sie diesen Entwurf wirklich löschen?")) {
            $.ajax({
                method: 'post',
                url: 'admin/onpage-composer.php',
                data: {
                    action: 'discard',
                    pageKey: draftKey,
                    jtl_token: '{$adminSessionToken}'
                },
                success: function(jqxhr, textStatus) {
                    if(jqxhr === 'ok') {
                        let draftItem = $('#opc-draft-' + draftKey);
                        draftItem.animate(
                            { opacity: 'toggle' },
                            500,
                            () => draftItem.remove()
                        );
                        window.localStorage.removeItem('opcpage.' + draftKey);
                    }
                }
            });
        }
    }
</script>
<div id="opc">
    {if $pageDrafts|count === 0}
        <nav id="opc-startmenu">
            <form method="post" action="admin/onpage-composer.php">
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
        <div id="opc-sidebar">
        <header id="opc-header">
            {*<button>*}
                {*<i class="fas fa-ellipsis-v"></i>*}
            {*</button>*}
            <h1 id="opc-sidebar-title">
                Seite bearbeiten
            </h1>
            <button onclick="closeOpcStartMenu()" class="float-right">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div id="opc-sidebar-content">
            <h2 id="opc-sidebar-second-title">Alle Entwürfe</h2>
                <ul id="opc-draft-list">
                    {foreach $pageDrafts as $i => $draft}
                        <li class="opc-draft" id="opc-draft-{$draft->getKey()}">
                            <form method="post" action="admin/onpage-composer.php">
                                <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                                <input type="hidden" name="pageKey" value="{$draft->getKey()}">
                                <button type="submit" name="action" value="edit" class="opc-btn-link opc-draft-name">
                                    {$draft->getName()}
                                </button>
                                <span class="opc-draft-status">
                                    <i class="fas fa-circle fa-xs"></i> ÖFFENTLICH
                                </span>
                                <div class="opc-draft-info">
                                    <div class="opc-draft-info-line">
                                        updated
                                    </div>
                                    <div class="opc-draft-info-line">
                                        published
                                    </div>
                                    <div class="opc-draft-actions">
                                        <button type="submit" name="action" value="edit">
                                            <i class="fa-lg fa-fw fas fa-pencil-alt"></i>
                                        </button>
                                        <button>
                                            <i class="fa-lg fa-fw far fa-clone"></i>
                                        </button>
                                        <button>
                                            <i class="fa-lg fa-fw fas fa-language"></i>
                                        </button>
                                        <button type="button" onclick="deleteOpcDraft({$draft->getKey()})">
                                            <i class="fa-lg fa-fw fas fa-trash"></i>
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