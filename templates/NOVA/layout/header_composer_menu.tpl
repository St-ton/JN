{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{function draftItem}
    {block name='layout-header-composer-menu-function-draft-item'}
        {if $isCurDraft}
            {assign var=draftTooltip value='Momentan öffentlich'}
            {if $draftPublishTo !== null}
                {assign var=draftTooltip value=$draftTooltip|cat:' bis '|cat:($draftPublishTo|date_format:'d.m.Y')}
            {/if}
        {elseif $draftPublishFrom !== null}
            {assign var=draftTooltip value='Öffentlich ab '|cat:($draftPublishFrom|date_format:'d.m.Y')}
        {else}
            {assign var=draftTooltip value='Entwurf'}
        {/if}
        {buttongroup class="mb-2 w-100"}
            {form method="post" action="admin/onpage-composer.php" class="w-100" addtoken=false}
                {input type="hidden" name="jtl_token" value=$adminSessionToken}
                {input type="hidden" name="pageKey" value=$draftKey}
                {button size="sm"
                    variant="danger"
                    class="opc-draft-item-discard float-right"
                    title="Entwurf löschen" role="button" id="btnDiscard{$draftKey}"}
                    <i class="fas fa-trash"></i>
                {/button}
                {button type="submit" name="action" value="edit" title="Entwurf bearbeiten"
                        variant="{if $isCurDraft}secondary{else}light{/if}" size="sm"}
                    {if $isCurDraft}
                        <b><i class="fas fa-newspaper"></i> {$draftName}</b>
                    {else}
                        <i class="fas fa-file"></i> {$draftName}
                    {/if}
                {/button}
                <script>
                    (function() {
                        var btnDiscard = $('#btnDiscard{$draftKey}');
                        btnDiscard.on('click', function(e) {
                            e.preventDefault();
                            if(confirm("Wollen Sie diesen Entwurf wirklich löschen?")) {
                                $.ajax({
                                    method: 'post',
                                    url: 'admin/onpage-composer.php',
                                    data: { action: 'discard', pageKey: {$draftKey}, jtl_token: '{$adminSessionToken}' },
                                    success: function(jqxhr, textStatus) {
                                        if(jqxhr === 'ok') {
                                            btnDiscard.closest('form').remove();
                                            window.localStorage.removeItem('opcpage.{$draftKey}');
                                        }
                                    }
                                });
                            }
                        });
                    })();
                </script>
            {/form}
        {/buttongroup}
    {/block}
{/function}

{block name='layout-header-composer-menu'}
    {assign var=curPage value=$opcPageService->getCurPage()}
    {assign var=curPageId value=$curPage->getId()}
    {assign var=pageDrafts value=$opcPageService->getDrafts($curPageId)}
    {assign var=curDraftKey value=$curPage->getKey()}
    {assign var=adminSessionToken value=$opc->getAdminSessionToken()}
    {assign var=otherLangDrafts value=$opcPageService->getOtherLanguageDrafts($curPageId)}

    {block name='layout-header-composer-menu-opc-switcher'}
        <div id="opc-switcher" class="d-none d-md-flex">
            <div class="switcher">
                <a href="#" class="parent btn btn-primary btn-toggle" aria-expanded="false" onclick="$('.switcher').toggleClass('open')">
                    <i class="fas fa-pencil-alt"></i>
                </a>
                <div class="switcher-wrapper">
                    <div class="switcher-header px-3">
                        <div class="h2">OnPage Composer</div>
                    </div>
                    <div class="switcher-content">
                        {if $pageDrafts|count > 0}
                            {listgroup}
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
                            {/listgroup}
                        {/if}
                        {if $pageDrafts|count > 0}
                            <p class="mt-3">
                                {button type="button" variant="link" size="sm" class="text-danger"
                                        title="Verwirft alle vorhandenen Entwürfe!" id="btnDiscardAll"}
                                    <i class="fas fa-trash"></i>
                                    Alle Entwürfe verwerfen
                                {/button}
                                {block name='layout-header-composer-menu-script-discard'}
                                    <script>
                                        (function() {
                                            var btnDiscardAll = $('#btnDiscardAll');
                                            btnDiscardAll.on('click', function(e) {
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
                                {/block}
                            </p>
                        {/if}
                        {block name='layout-header-composer-menu-form-edit'}
                            {form method="post" action="admin/onpage-composer.php" addtoken=false}
                                {input type="hidden" name="jtl_token" value=$adminSessionToken}
                                {input type="hidden" name="pageId" value=$curPage->getId()}
                                {input type="hidden" name="pageUrl" value=$curPage->getUrl()}
                                <div class="btn-group">
                                    {button type="submit" name="action" value="extend" variant="primary"}
                                        <i class="fas fa-plus-circle"></i>
                                        Neuer Entwurf
                                    {/button}
                                </div>
                            {/form}
                        {/block}
                        {if $otherLangDrafts|count > 0}
                            {block name='layout-header-composer-menu-form-lang'}
                                {form method="post" action="admin/onpage-composer.php"}
                                    {input type="hidden" name="jtl_token" value=$adminSessionToken}
                                    {input type="hidden" name="pageId" value=$curPage->getId()}
                                    {input type="hidden" name="pageUrl" value=$curPage->getUrl()}
                                    {input type="hidden" name="action" value="adopt"}
                                    <div class="btn-group">
                                        {button variant="primary" class="dropdown-toggle" data=['toggle' => 'dropdown']
                                                aria=['haspopup' => 'true', 'expanded' => 'false']}
                                            <i class="fas fa-language"></i>
                                            Aus anderer Sprache übernehmen <span class="caret"></span>
                                        {/button}
                                        <ul class="dropdown-menu">
                                            {foreach $otherLangDrafts as $draft}
                                                <li>
                                                    {button type="submit" name="adoptFromKey" value=$draft->kPage variant='link'}
                                                        <b>{$draft->cNameEnglisch}</b> : {$draft->cName}
                                                    {/button}
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                {/form}
                            {/block}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {/block}
{/block}
