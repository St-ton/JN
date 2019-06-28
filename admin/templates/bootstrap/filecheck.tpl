{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='filecheck'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('filecheck') cBeschreibung=__('filecheckDesc') cDokuURL=__('filecheckURL')}

{$alertList->displayAlertByKey('orphanedFilesError')}
{$alertList->displayAlertByKey('modifiedFilesError')}
{$alertList->displayAlertByKey('backupMessage')}
{$alertList->displayAlertByKey('zipArchiveError')}

<div class="panel collapsed">
    <div class="panel-heading{if $modifiedFiles|count > 0} accordion-toggle" data-toggle="collapse" data-target="#pageCheckModifiedFiles" style="cursor:pointer"{else}"{/if}>
        <h3 class="panel-title">
            {if $modifiedFiles|count > 0}<i class="fa fas fa-plus"></i> {/if}
            {__('fileCheckNumberModifiedFiles')}: {$modifiedFiles|count}
        </h3>
    </div>
    {if $modifiedFiles|count > 0}
        <div class="panel-body panel-collapse collapse" id="pageCheckModifiedFiles">
            <p class="small text-muted">{__('fileCheckModifiedFilesNote')}</p>
            <div id="contentModifiedFilesCheck">
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
        </div>
    {/if}
</div>
<div class="panel collapsed">
    <div class="panel-heading{if $orphanedFiles|count > 0} accordion-toggle" data-toggle="collapse" data-target="#pageCheckOrphanedFiles" style="cursor:pointer"{else}"{/if}>
        <h3 class="panel-title">
            {if $orphanedFiles|count > 0}<i class="fa fas fa-plus"></i> {/if}
            {__('fileCheckNumberOrphanedFiles')}: {$orphanedFiles|count}
        </h3>
    </div>
    {if $orphanedFiles|count > 0}
        <div class="panel-body panel-collapse collapse" id="pageCheckOrphanedFiles">
            <p class="small text-muted">{__('fileCheckOrphanedFilesNote')}</p>
            <div id="contentOrphanedFilesCheck">
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
                <form method="post">
                    {$jtl_token}
                    <button class="btn btn-danger" name="delete-orphans" value="1" onclick="return confirmDelete();">
                        <i class="fa fas fa-trash"></i> {__('delete')}
                    </button>
                </form>
            </div>
        </div>
    {/if}
</div>
<script type="text/javascript">
    function confirmDelete() {ldelim}
        return confirm('{__('confirmDeleteText')}');
    {rdelim}
</script>
{include file='tpl_inc/footer.tpl'}
