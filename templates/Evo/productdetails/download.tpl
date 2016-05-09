{if isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0}
<div id="article_downloads">
   <table class="table table-striped">
      <thead>
         <tr>
            <th></th>
            <th>Name</th>
            <th>Beschreibung</th>
            <th>Format</th>
            <th>Aktionen</th>
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

                     {if $oDownload->getPreviewType() === 'music' || $oDownload->getPreviewType() === 'video'}
                        <a href="#" onclick="return open_window('{$ShopURL}/popup.php?a=download_vorschau&k={$oDownload->getDownload()}', 480, 320);" class="btn_play">x</a>
                     {elseif $oDownload->getPreviewType() === 'image'}

                        {assign var=nNameLength value=50}
                        {assign var=nImageMaxWidth value=480}
                        {assign var=nImageMaxHeight value=320}
                        {assign var=nImagePreviewWidth value=35}

                        <span class="image_preview zoomcur" ref="{$oDownload->getPreview()}" maxwidth="{$nImageMaxWidth}" maxheight="{$nImageMaxHeight}" title="{$oDownload->oDownloadSprache->getName()}">
                           <img src="{$oDownload->getPreview()}" alt="{$oUpload->cName}" width="{$nImagePreviewWidth}" class="vmiddle" />
                        </span>
                     {else}

                        {* nothing to do *}

                     {/if}

                  {/if}
               </td>
            </tr>
         {/if}
         {/foreach}
      </tbody>
   </table>
</div>
{/if}