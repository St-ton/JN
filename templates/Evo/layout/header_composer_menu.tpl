{function draftItem}
    {assign var="queryDraft" value=$query|cat:"&pageKey="|cat:$draftKey}

    {if $isCurDraft}
        {assign var="draftTooltip" value="Momentan öffentlich"}
        {if $draftPublishTo !== null}
            {assign var="draftTooltip" value=$draftTooltip|cat:" bis "|cat:($draftPublishTo|date_format:"d.m.Y")}
        {/if}
    {elseif $draftPublishFrom !== null}
        {assign var="draftTooltip" value="Öffentlich ab "|cat:($draftPublishFrom|date_format:"d.m.Y")}
    {else}
        {assign var="draftTooltip" value="Entwurf"}
    {/if}

    <div class="list-group-item{if $isCurDraft} list-group-item-success{/if}" data-toggle="tooltip"
         data-placement="right" title="{$draftTooltip}">
        <a href="admin/onpage-composer.php{$queryDraft}&action=edit" class="btn btn-sm" title="Entwurf bearbeiten">
            {if $isCurDraft}
                <b><i class="fa fa-fw fa-newspaper-o"></i> {$draftName}</b>
            {else}
                <i class="fa fa-fw fa-file-o"></i> {$draftName}
            {/if}
        </a>
        <a href="admin/onpage-composer.php{$queryDraft}&action=discard"
           class="btn btn-sm btn-danger opc-draft-item-discard pull-right"
           title="Entwurf löschen" id="btnDiscard{$draftKey}">
            <i class="fa fa-times"></i>
        </a>
        <script>
            (function() {
                var btnDiscard = $('#btnDiscard{$draftKey}');
                btnDiscard.click(function(e) {
                    e.preventDefault();
                    if(confirm("Wollen Sie diesen Entwurf wirklich löschen?")) {
                        var href = btnDiscard.attr('href');
                        $.ajax(href + '&async=yes', {
                            success: function(jqxhr, textStatus) {
                                if(jqxhr === 'ok') {
                                    btnDiscard.closest('.list-group-item').remove();
                                }
                            }
                        });
                    }
                });
            })();
        </script>
    </div>
{/function}

{assign var="curPage" value=$opcPageService->getCurPage()}
{assign var="curPageId" value=$curPage->getId()}
{assign var="pageDrafts" value=$opcPageService->getDrafts($curPageId)}
{assign var="curDraftKey" value=$curPage->getKey()}

<div id="opc-switcher">
    <div class="switcher" id="dashboard-config">
        <a href="#" class="parent btn-toggle" aria-expanded="false" onclick="$('.switcher').toggleClass('open')">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="switcher-wrapper">
            <div class="switcher-header">
                <h2>OnPage Composer</h2>
            </div>
            <div class="switcher-content">
                {assign var="query" value="?token="|cat:$smarty.session.jtl_token}

                {if $pageDrafts|count > 0}
                    <div class="list-group">
                        {if $curDraftKey > 0}
                            {call draftItem isCurDraft=true
                                query=$query
                                draftKey=$curDraftKey
                                draftPublishFrom=$curPage->getPublishFrom()
                                draftPublishTo=$curPage->getPublishTo()
                                draftName=$curPage->getName()}
                        {/if}

                        {foreach $pageDrafts as $i => $draft}
                            {if $curDraftKey != $draft->getKey()}
                                {call draftItem isCurDraft=false
                                    query=$query
                                    draftKey=$draft->getKey()
                                    draftPublishFrom=$draft->getPublishFrom()
                                    draftPublishTo=$draft->getPublishTo()
                                    draftName=$draft->getName()}
                            {/if}
                        {/foreach}
                    </div>
                {/if}

                {assign var="query" value=$query|cat:"&pageId="|cat:$curPage->getId()}
                {assign var="query" value=$query|cat:"&pageUrl="|cat:$curPage->getUrl()}

                {if $pageDrafts|count > 0}
                    <p>
                        <a href="admin/onpage-composer.php{$query}&action=restore" class="btn btn-sm btn-danger"
                           title="Verwirft alle vorhandenen Entwürfe!" id="btnDiscardAll">
                            <i class="fa fa-times"></i>
                            Alle Entwürfe verwerfen
                        </a>
                        <script>
                            (function() {
                                var btnDiscardAll = $('#btnDiscardAll');
                                btnDiscardAll.click(function(e) {
                                    e.preventDefault();
                                    if(confirm('Wollen Sie wirklich alle Entwürfe für die Seite löschen?')) {
                                        var href = btnDiscardAll.attr('href');
                                        $.ajax(href + '&async=yes', {
                                            success: function(jqxhr, textStatus) {
                                                if(jqxhr === 'ok') {
                                                    btnDiscardAll.closest('p').remove();
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

                <div class="btn-group">
                    <a href="admin/onpage-composer.php{$query}&action=extend" class="btn btn-sm btn-primary"
                       title="Die aktuelle Seite in einem neuem Entwurf erweitern">
                        <i class="fa fa-plus-circle"></i>
                        Seite erweitern
                    </a>
                    <a href="admin/onpage-composer.php{$query}&action=replace" class="btn btn-sm btn-primary"
                       title="Die aktuelle Seite in einem neuem Entwurf ersetzen">
                        <i class="fa fa-file-o"></i>
                        Seite ersetzen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>