 <div role="tabpanel" class="{if $tabanzeige}tab-pane{else}panel panel-default{/if}" id="article_downloads">
    <div class="panel-heading" {if $tabanzeige}data-toggle="collapse" {/if}data-parent="#article-tabs" data-target="#article_downloads">
        <h3 class="panel-title">{lang section="productDownloads" key="downloadSection"}</h3>
    </div>
    <div class="tab-content-wrapper">
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th></th>
                    <th>{lang section="productDownloads" key="downloadName"}</th>
                    <th>{lang section="productDownloads" key="downloadDescription" }</th>
                    <th>{lang section="productDownloads" key="downloadFileExt"}</th>
                    <th>{lang section="productDownloads" key="downloadPreview"}</th>
                </tr>
                </thead>
                <tbody>
                {foreach name=downloads from=$Artikel->oDownload_arr item=oDownload}
                    {if isset($oDownload->oDownloadSprache)}
                        <tr>
                            <td>{$smarty.foreach.downloads.index+1}.</td>
                            <td>{$oDownload->oDownloadSprache->getName()}</td>
                            <td>{$oDownload->oDownloadSprache->getBeschreibung()}</td>
                            <td>{$oDownload->getExtension()}</td>
                            <td>
                                {if $oDownload->hasPreview()}
                                    {if $oDownload->getPreviewType() === 'music'}
                                        <audio controls>
                                            <source src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" >
                                            Your browser does not support the audio element.
                                        </audio>
                                    {elseif $oDownload->getPreviewType() === 'video'}
                                        <video width="320" height="240" controls>
                                            <source src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" >
                                            Your browser does not support the video tag.
                                        </video>
                                    {elseif $oDownload->getPreviewType() === 'image'}
                                        <img src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" class="img-responsive" alt="{$oDownload->oDownloadSprache->getBeschreibung()}">
                                    {else}
                                        <video width="320" height="240" controls>
                                            <source src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" >
                                            Your browser does not support the video tag.
                                        </video>
                                        <audio controls>
                                            <source src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" >
                                            Your browser does not support the audio element.
                                        </audio>
                                    {/if}
                                {/if}
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
