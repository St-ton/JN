{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('Localization check') cBeschreibung=__('localizationCheckDesc') cDokuURL=__('localizationCheckURL')}
<div id="content">
    <style>.card-title { margin-bottom: .5rem } </style>
    {if $safe_mode}
        <div class="alert alert-info">
            {__('Safe mode enabled.')}
        </div>
    {else}
        <div class="systemcheck">
            {if !$passed}
                <div class="alert alert-info">
                    {__('Localization problems found.')}
                </div>
                <hr>
                {foreach $checkResults as $result}
                    {$failed = $result->hasPassed() === false}
                    <div class="card collapsed{if !$failed} text-white bg-success{/if}">
                        <div {if $failed}class="card-header accordion-toggle" data-toggle="collapse" data-target="#check-{$result@index}" style="cursor:pointer"{else}class="card-header"{/if}>
                        <div class="card-title">
                            {if $failed}
                                <i class="fa fas fa-plus"></i>
                            {/if}
                            {__($result->getClassName())} &ndash; {sprintf(__('%d errors'), $result->getErrorCount())}
                        </div>
                    </div>
                    {if $failed}
                        <div class="card-body collapse" id="check-{$result@index}">
                            {$excess = $result->getExcessLocalizations()}
                            {$missing = $result->getMissingLocalizations()}
                            {if $excess->count() > 0}
                                <h2>{__('Excess translations')}</h2>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless table-striped">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="text-left">{__('ID')}</th>
                                            <th scope="col" class="text-left">{__('Language ID')}</th>
                                            <th scope="col" class="text-left">{__('Name')}</th>
                                        </tr>
                                        </thead>
                                        {foreach $excess as $item}
                                            <tr>
                                                <td>{$item->getID()}</td>
                                                <td>{$item->getLanguageID()}{if $item->getAdditional() !== null} {$item->getAdditional()}{/if}</td>
                                                <td>{$item->getName()}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                                <form method="post" action="{$adminURL}/{JTL\Router\Route::LOCALIZATION_CHECK}">
                                    {$jtl_token}
                                    <input type="hidden" name="action" value="deleteExcess">
                                    <input type="hidden" name="type" value="{$result->getClassName()}">
                                    <button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> {__('Delete')}</button>
                                </form>
                                <hr>
                            {/if}
                            {if $missing->count() > 0}
                                <h2>{__('Missing translations')}</h2>
                                <span class="path">{__('Can be found: ')}{htmlspecialchars($result->getLocation())}</span>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless table-striped">
                                        <thead>
                                        <tr>
                                            <th scope="col">{__('ID')}</th>
                                            <th scope="col" >{__('Language ID')} - ISO</th>
                                            <th scope="col">{__('Name')}</th>
                                        </tr>
                                        </thead>
                                        {foreach $missing as $item}
                                            <tr>
                                                <td>{$item->getID()}</td>
                                                <td>{$item->getLanguageID()} - {$languagesById[$item->getLanguageID()]->iso}</td>
                                                <td>{$item->getName()}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            {/if}
                        </div>
                    {/if}
                    </div>
                {/foreach}
            {else}
                <div class="alert alert-info">{__('infoNoOrphanedCats')}</div>
            {/if}
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
