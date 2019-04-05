{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='filecheck'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('filecheck') cBeschreibung=__('filecheckDesc') cDokuURL=__('filecheckURL')}
{if $modifiedFilesCheck}
    <div class="panel panel-collapse">
        <div class="panel-heading">
            {__('fileCheckModifiedFilesHeadline')}
            <p class="small text-muted">{__('fileCheckModifiedFilesNote')}</p>
        </div>
        <div class="panel-body">
            <div id="content" class="container-fluid">
                <div id="pageCheckModifiedFiles">
                    {$alertList->displayAlertByKey('modifiedFilesError')}
                    {if !$modifiedFilesError}
                        {if isset($modifiedFiles) && $modifiedFiles|@count > 0}
                            <div id="contentModifiedFilesCheck">
                                <div class="alert alert-info">
                                    <strong>{__('fileCheckNumberModifiedFiles')}:</strong> {$errorsCounModifiedFiles}
                                </div>
                                <table class="table req">
                                    <thead>
                                    <tr>
                                        <th>{__('file')}</th>
                                    </tr>
                                    </thead>
                                    {foreach $modifiedFiles as $file}
                                        <tr class="filestate mod{$file@iteration % 2} modified">
                                            <td>{$file}</td>
                                        </tr>
                                    {/foreach}
                                </table>
                            </div>
                        {else}
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
{if $orphanedFilesCheck}
    <div class="panel panel-collapse">
        <div class="panel-heading">
            {__('fileCheckOrphanedFilesHeadline')}
            <p class="small text-muted">{__('fileCheckOrphanedFilesNote')}</p>
        </div>
        <div class="panel-body">
            <div id="content" class="container-fluid">
                <div id="pageCheckOrphanedFiles">
                    {$alertList->displayAlertByKey('orphanedFilesError')}
                    {if !$orphanedFilesError}
                        {if isset($orphanedFiles) && $orphanedFiles|@count > 0}
                            <div id="contentOrphanedFilesCheck">
                                <div class="alert alert-info">
                                    <strong>{__('fileCheckNumberOrphanedFiles')}:</strong> {$errorsCountOrphanedFiles}
                                </div>
                                <table class="table req">
                                    <thead>
                                        <tr>
                                            <th>{__('file')}</th>
                                        </tr>
                                    </thead>
                                    {foreach $orphanedFiles as $file}
                                        <tr class="filestate mod{$file@iteration % 2} orphaned">
                                            <td>{$file}</td>
                                        </tr>
                                    {/foreach}
                                </table>
                            </div>
                        {else}
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
{include file='tpl_inc/footer.tpl'}
