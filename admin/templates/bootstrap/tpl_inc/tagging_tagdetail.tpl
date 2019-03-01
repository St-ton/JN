{include file='tpl_inc/seite_header.tpl' cTitel=__('taggingdetail') cBeschreibung=__('taggingdetailDesc')}
<div id="content">
    {if !empty($cTagName)}
        <p>{__('taggingdetailTag')} <strong>{$cTagName}</strong></p>
    {else}
        <p class="alert alert-info">{__('noDataAvailable')}</p>
    {/if}
    {if isset($oTagArtikel_arr) && $oTagArtikel_arr|@count > 0}
        {include file='tpl_inc/pagination.tpl' oPagination=$oPagiTagDetail}
        <!-- Tag Detailansicht -->
        <form method="post" action="tagging.php">
            {$jtl_token}
            <input name="detailloeschen" type="hidden" value="1" />
            <input name="tagdetail" type="hidden" value="1" />
            <input type="hidden" name="kTag" value="{$kTag}" />

            <div id="payment">
                <div id="tabellenLivesuche" class="table-responsive">
                    <table class="table table-striped">
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="th-2">{__('taggingProduct')}</th>
                        </tr>
                        {foreach $oTagArtikel_arr as $oTagArtikel}
                            <tr>
                                <td class="check">
                                    <input name="kArtikel_arr[]" type="checkbox" value="{$oTagArtikel->kArtikel}" />
                                </td>
                                <td><a href="{$oTagArtikel->cURL}">{$oTagArtikel->acName}</a></td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td class="check">
                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                            </td>
                            <td colspan="5"><label for="ALLMSGS">{__('selectAll')}</label></td>
                        </tr>
                    </table>
                </div>
            </div>
            <p class="submit">
                <button name="loeschen" type="submit" value="{__('taggingdelete')}" class="btn btn-danger"><i class="fa fa-trash"></i> {__('taggingdelete')}</button>
            </p>
        </form>
    {/if}
</div>