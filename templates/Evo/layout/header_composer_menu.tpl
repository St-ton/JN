{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{function draftItem}
    {if $isCurDraft}
        {assign var='draftTooltip' value='Momentan öffentlich'}
        {if $draftPublishTo !== null}
            {assign var='draftTooltip' value=$draftTooltip|cat:' bis '|cat:($draftPublishTo|date_format:'d.m.Y')}
        {/if}
    {elseif $draftPublishFrom !== null}
        {assign var='draftTooltip' value='Öffentlich ab '|cat:($draftPublishFrom|date_format:'d.m.Y')}
    {else}
        {assign var='draftTooltip' value='Entwurf'}
    {/if}

    <form method="post" action="admin/onpage-composer.php"
          class="list-group-item list-group-item-{if $isCurDraft}success{else}default{/if}"
          data-toggle="tooltip" data-placement="right" data-draft-key="{$draftKey}" title="{$draftTooltip}">
        <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
        <input type="hidden" name="pageKey" value="{$draftKey}">
        <button type="submit" name="action" value="edit" title="Entwurf bearbeiten" class="btn-link">
            {if $isCurDraft}
                <b><i class="fa fa-fw fa-newspaper-o"></i> {$draftName}</b>
            {else}
                <i class="fa fa-fw fa-file-o"></i> {$draftName}
            {/if}
        </button>
        <button type="button"
           class="btn btn-sm btn-danger opc-draft-item-discard pull-right"
           title="Entwurf löschen" role="button" id="btnDiscard{$draftKey}">
            <i class="fa fa-trash"></i>
        </button>
        <script>
            (function() {
                var btnDiscard = $('#btnDiscard{$draftKey}');
                btnDiscard.click(function(e) {
                    e.preventDefault();
                    if(confirm("Wollen Sie diesen Entwurf wirklich löschen?")) {
                        $.ajax({
                            method: 'post',
                            url: 'admin/onpage-composer.php',
                            data: { action: 'discard', pageKey: {$draftKey}, jtl_token: '{$adminSessionToken}' },
                            success: function(jqxhr, textStatus) {
                                if(jqxhr === 'ok') {
                                    btnDiscard.closest('.list-group-item').remove();
                                    window.localStorage.removeItem('opcpage.{$draftKey}');
                                }
                            }
                        });
                    }
                });
            })();
        </script>
    </form>
{/function}

{assign var="curPage" value=$opcPageService->getCurPage()}
{assign var="curPageId" value=$curPage->getId()}
{assign var="pageDrafts" value=$opcPageService->getDrafts($curPageId)}
{assign var="curDraftKey" value=$curPage->getKey()}
{assign var="adminSessionToken" value=$opc->getAdminSessionToken()}

<div id="opc-switcher">
    <div class="switcher">
        <a href="#" class="parent btn-toggle" aria-expanded="false" onclick="$('.switcher').toggleClass('open')">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="switcher-wrapper">
            <div class="switcher-header">
                <h2>OnPage Composer</h2>
            </div>
            <div class="switcher-content">
                {if $pageDrafts|count > 0}
                    <div class="list-group">
                        {if $curDraftKey > 0}
                            {call draftItem isCurDraft=true
                                draftKey=$curDraftKey
                                draftPublishFrom=$curPage->getPublishFrom()
                                draftPublishTo=$curPage->getPublishTo()
                                draftName=$curPage->getName()}
                        {/if}

                        {foreach $pageDrafts as $i => $draft}
                            {if $curDraftKey != $draft->getKey()}
                                {call draftItem isCurDraft=false
                                    draftKey=$draft->getKey()
                                    draftPublishFrom=$draft->getPublishFrom()
                                    draftPublishTo=$draft->getPublishTo()
                                    draftName=$draft->getName()}
                            {/if}
                        {/foreach}
                    </div>
                {/if}
                {if $pageDrafts|count > 0}
                    <p>
                        <button type="button" class="btn btn-sm btn-danger"
                           title="Verwirft alle vorhandenen Entwürfe!" id="btnDiscardAll">
                            <i class="fa fa-trash"></i>
                            Alle Entwürfe verwerfen
                        </button>
                        <script>
                            (function() {
                                var btnDiscardAll = $('#btnDiscardAll');
                                btnDiscardAll.click(function(e) {
                                    e.preventDefault();
                                    var keys = $('#opc-switcher .list-group .list-group-item')
                                        .map(function() { return $(this).data('draft-key'); });
                                    if(confirm('Wollen Sie wirklich alle Entwürfe für die Seite löschen?')) {
                                        $.ajax({
                                            method: 'post',
                                            url: 'admin/onpage-composer.php',
                                            data: {
                                                action: 'restore', pageId: '{$curPageId}',
                                                jtl_token: '{$adminSessionToken}'
                                            },
                                            success: function(jqxhr, textStatus) {
                                                if(jqxhr === 'ok') {
                                                    btnDiscardAll.closest('p').remove();
                                                    keys.each(function(i, key) {
                                                        window.localStorage.removeItem('opcpage.' + key);
                                                    });
                                                    $('#opc-switcher .list-group').remove();
                                                }
                                            }
                                        });
                                    }
                                });
                            })();
                        </script>
                    </p>
                    <p><label>Neuer Entwurf:</label></p>
                {/if}

                <form class="btn-group" method="post" action="admin/onpage-composer.php">
                    <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                    <input type="hidden" name="pageId" value="{$curPage->getId()}">
                    <input type="hidden" name="pageUrl" value="{$curPage->getUrl()}">
                    <button type="submit" name="action" value="extend" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus-circle"></i>
                        Seite erweitern
                    </button>
                    <button type="submit" name="action" value="replace" class="btn btn-sm btn-primary">
                        <i class="fa fa-file-o"></i>
                        Seite ersetzen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
