{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-download'}
<div class="card-columns">
    {foreach $Artikel->oDownload_arr as $oDownload}
        {if isset($oDownload->oDownloadSprache)}
            {card title=$oDownload->oDownloadSprache->getName() class="mb-3"}
                {row}
                    {block name='productdetails-download-description'}
                        {col cols=12}
                            {$oDownload->oDownloadSprache->getBeschreibung()}
                        {/col}
                    {/block}
                    {if $oDownload->hasPreview()}
                        {block name='productdetails-download-preview'}
                            {col cols=12}
                                {if $oDownload->getPreviewType() === 'music'}
                                    <audio controls controlsList="nodownload" preload="none">
                                        <source src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" >
                                        Your browser does not support the audio element.
                                    </audio>
                                {elseif $oDownload->getPreviewType() === 'video'}
                                    <video width="320" height="240" controls controlsList="nodownload" preload="none">
                                        <source src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}" >
                                        Your browser does not support the video tag.
                                    </video>
                                {elseif $oDownload->getPreviewType() === 'image'}
                                    {image src="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}"
                                         fluid=true alt=$oDownload->oDownloadSprache->getBeschreibung()|strip_tags}
                                {else}
                                    {link href="{PFAD_DOWNLOADS_PREVIEW_REL}{$oDownload->cPfadVorschau}"
                                       title="{$oDownload->oDownloadSprache->getName()}" target="_blank"}
                                        {$oDownload->oDownloadSprache->getName()}
                                    {/link}
                                {/if}
                            {/col}
                        {/block}
                    {/if}
                {/row}
            {/card}
        {/if}
    {/foreach}
</div>
{/block}
