{foreach $pageDrafts as $i => $draft}
    {$draftStatus = $draft->getStatus($publicDraftKey)}
    <li class="opc-draft" id="opc-draft-{$draft->getKey()}" data-draft-status="{$draftStatus}"
        data-draft-name="{$draft->getName()}" data-draft-key="{$draft->getKey()}">
        <input type="checkbox" id="check-{$draft->getKey()}" onchange="opcDraftCheckboxChanged()"
               class="draft-checkbox filtered-draft">
        <label for="check-{$draft->getKey()}" class="opc-draft-name" title="{$draft->getName()}">
            {$draft->getName()}
        </label>
        {if $draftStatus === 0}
            <span class="opc-draft-status opc-public">
                <i class="fa fas fa-circle fa-xs"></i> {__('publicUpper')}
            </span>
        {elseif $draftStatus === 1}
            <span class="opc-draft-status opc-planned">
                <i class="fa fas fa-circle fa-xs"></i> {__('plannedUpper')}
            </span>
        {elseif $draftStatus === 2}
            <span class="opc-draft-status opc-status-draft">
                <i class="fa fas fa-circle fa-xs"></i> {__('draftUpper')}
            </span>
        {elseif $draftStatus === 3}
            <span class="opc-draft-status opc-backdate">
                <i class="fa fas fa-circle fa-xs"></i> {__('pastUpper')}
            </span>
        {/if}
        <div class="opc-draft-info">
            <div class="opc-draft-info-line">
                {if $draftStatus === 0}
                    {if $draft->getPublishTo() === null}
                        <span class="opc-public">{__('publicSince')}</span>
                        {$draft->getPublishFrom()|date_format:'%d.%m.%Y - %H:%M'}
                    {else}
                        <span class="opc-public">{__('publicUntill')}</span>
                        {$draft->getPublishTo()|date_format:'%d.%m.%Y - %H:%M'}
                    {/if}
                {elseif $draftStatus === 1}
                    <span class="opc-planned">{__('plannedFrom')}</span>
                    {$draft->getPublishFrom()|date_format:'%d.%m.%Y - %H:%M'}
                {elseif $draftStatus === 2}
                    <span class="opc-status-draft">{__('noPublicationPlanned')}</span>
                {elseif $draftStatus === 3}
                    <span class="opc-backdate">{__('expiredOn')}</span>
                    {$draft->getPublishTo()|date_format:'%d.%m.%Y - %H:%M'}
                {/if}
            </div>
            <div class="opc-draft-actions">
                <form method="post" action="{$opcStartUrl}">
                    <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                    <input type="hidden" name="pageKey" value="{$draft->getKey()}">
                    <button type="submit" name="action" value="edit" data-toggle="tooltip"
                            title="Bearbeiten" data-placement="bottom" data-container="#opc">
                        <i class="fa fa-lg fa-fw fas fa-pencil-alt fa-pencil"></i>
                    </button>
                </form>
                <button type="button" onclick="duplicateOpcDraft({$draft->getKey()})"
                        data-toggle="tooltip" title="{__('duplicate')}" data-placement="bottom"
                        data-container="#opc">
                    <i class="fa fa-lg fa-fw far fa-clone"></i>
                </button>
                <div class="opc-dropdown">
                    <button type="button"
                            data-toggle="dropdown" title="{__('useForOtherLang')}"
                            data-placement="bottom" data-container="#opc">
                        <img src="{$ShopURL}/admin/opc/gfx/icon-copysprache.svg">
                    </button>
                    <div class="dropdown-menu opc-dropdown-menu">
                        {foreach $languages as $lang}
                            {if $lang->id !== $currentLanguage->id}
                                {if isset($lang->pageId)}
                                    {$langPageId = $lang->pageId}
                                {else}
                                    {$langPageId = $opcPageService->createCurrentPageId($lang->id)}
                                {/if}
                                {if isset($lang->pageUri)}
                                    {$langPageUri = $lang->pageUri}
                                {else}
                                    {$langPageUri = $opcPageService->getCurPageUri($lang->id)}
                                {/if}
                                <form method="post"
                                      action="{$opcStartUrl}">
                                    <input type="hidden" name="jtl_token" value="{$adminSessionToken}">
                                    <input type="hidden" name="action" value="adopt">
                                    <input type="hidden" name="pageKey" value="{$draft->getKey()}">
                                    <input type="hidden" name="pageId" value="{$langPageId}">
                                    <input type="hidden" name="pageName" value="{$draft->getName()} ({$lang->nameDE})">
                                    <button type="submit" name="pageUrl" class="opc-dropdown-item"
                                            value="{$langPageUri}">
                                        {$lang->nameDE}
                                    </button>
                                </form>
                            {/if}
                        {/foreach}
                    </div>
                </div>
                <button type="button" onclick="deleteOpcDraft({$draft->getKey()})"
                        data-toggle="tooltip" title="{__('delete')}"
                        data-placement="bottom" data-container="#opc">
                    <i class="fa fa-lg fa-fw fas fa-trash"></i>
                </button>
            </div>
        </div>
    </li>
{/foreach}