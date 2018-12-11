{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="filecheck"}
{include file='tpl_inc/seite_header.tpl' cTitel=__("filecheck") cBeschreibung=__("filecheckDesc") cDokuURL=__("filecheckURL")}
{$modifiedFilesCheck = !empty($modifiedFilesError) || isset($modifiedFiles) && $modifiedFiles|@count > 0}
{$orphanedFilesCheck = !empty($orphanedFilesError) || isset($orphanedFiles) && $orphanedFiles|@count > 0}

{if !$modifiedFilesCheck && !$orphanedFilesCheck}
    <div class="alert alert-info">{__("fileCheckNoneModifiedOrphanedFiles")}</div>
{/if}
{if $modifiedFilesCheck}
    <div class="panel panel-collapse">
        <div class="panel-heading">
            {__("fileCheckModifiedFilesHeadline")}
            <p class="small text-muted">{__("fileCheckModifiedFilesNote")}</p>
        </div>
        <div class="panel-body">
            <div id="content" class="container-fluid">
                <div id="pageCheckModifiedFiles">
                    {if !empty($modifiedFilesError)}
                        <div class="alert alert-danger"><i class="fa fa-warning"></i> {$modifiedFilesError}</div>
                    {else}
                        {if isset($modifiedFiles) && $modifiedFiles|@count > 0}
                            <div id="contentModifiedFilesCheck">
                                <div class="alert alert-info">
                                    <strong>{__("fileCheckNumberModifiedFiles")}:</strong> {$errorsCounModifiedFiles}
                                </div>
                                <table class="table req">
                                    <thead>
                                    <tr>
                                        <th>{__("fileCheckFile")}</th>
                                    </tr>
                                    </thead>
                                    {foreach name=datei from=$modifiedFiles item=file}
                                        <tr class="filestate mod{$smarty.foreach.datei.iteration%2} modified">
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
            {__("fileCheckOrphanedFilesHeadline")}
            <p class="small text-muted">{__("fileCheckOrphanedFilesNote")}</p>
        </div>
        <div class="panel-body">
            <div id="content" class="container-fluid">
                <div id="pageCheckOrphanedFiles">
                    {if !empty($orphanedFilesError)}
                        <div class="alert alert-danger"><i class="fa fa-warning"></i> {$orphanedFilesError}</div>
                    {else}
                        {if isset($orphanedFiles) && $orphanedFiles|@count > 0}
                            <div id="contentOrphanedFilesCheck">
                                <div class="alert alert-info">
                                    <strong>{__("fileCheckNumberOrphanedFiles")}:</strong> {$errorsCountOrphanedFiles}
                                </div>
                                <table class="table req">
                                    <thead>
                                        <tr>
                                            <th>{__("fileCheckFile")}</th>
                                        </tr>
                                    </thead>
                                    {foreach name=datei from=$orphanedFiles item=file}
                                        <tr class="filestate mod{$smarty.foreach.datei.iteration%2} orphaned">
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