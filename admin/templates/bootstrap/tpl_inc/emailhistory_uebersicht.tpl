{include file='tpl_inc/seite_header.tpl' cTitel=#emailhistory# cBeschreibung=#emailhistoryDesc# cDokuURL="#emailhistoryURL#"}
<div id="content" class="container-fluid">
    {if $oEmailhistory_arr|@count > 0 && $oEmailhistory_arr}
        {include file='pagination.tpl' cSite=1 cUrl='emailhistory.php' oBlaetterNavi=$oBlaetterNaviUebersicht hash=''}
        <form name="emailhistory" method="post" action="emailhistory.php">
            {$jtl_token}
            <input name="a" type="hidden" value="delete" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#emailhistory#}</h3>
                </div>
                <table class="list table">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="tleft">{#subject#}</th>
                        <th class="tleft">{#fromname#}</th>
                        <th class="tleft">{#fromemail#}</th>
                        <th class="tleft">{#toname#}</th>
                        <th class="tleft">{#toemail#}</th>
                        <th class="tleft">{#date#}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=emailhistory from=$oEmailhistory_arr item=oEmailhistory}
                        <tr class="tab_bg{$smarty.foreach.emailhistory.iteration%2}">
                            <td class="check">
                                <input type="checkbox" name="kEmailhistory[]" value="{$oEmailhistory->getEmailhistory()}" />
                            </td>
                            <td>{$oEmailhistory->getSubject()}</td>
                            <td>{$oEmailhistory->getFromName()}</td>
                            <td>{$oEmailhistory->getFromEmail()}</td>
                            <td>{$oEmailhistory->getToName()}</td>
                            <td>{$oEmailhistory->getToEmail()}</td>
                            <td>{SmartyConvertDate date=$oEmailhistory->getSent()}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                    <tfoot>
                    <tr>
                        <td class="check">
                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" /></td>
                        <td colspan="8"><label for="ALLMSGS">Alle ausw&auml;hlen</label></td>
                    </tr>
                    </tfoot>
                </table>
                <div class="panel-footer">
                    <button name="zuruecksetzenBTN" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info">{#nodata#}</div>
    {/if}
</div>