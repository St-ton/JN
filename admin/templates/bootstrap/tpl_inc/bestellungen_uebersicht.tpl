{include file='tpl_inc/seite_header.tpl' cTitel=__('order') cBeschreibung=__('orderDesc') cDokuURL=__('orderURL')}
<div id="content">
    {if $oBestellung_arr|@count > 0 && $oBestellung_arr}
        <form name="bestellungen" method="post" action="bestellungen.php">
            {$jtl_token}
            <input type="hidden" name="zuruecksetzen" value="1" />
            {if isset($cSuche) && $cSuche|strlen > 0}
                <input type="hidden" name="cSuche" value="{$cSuche}" />
            {/if}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('order')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive card-body">
                    {include file='tpl_inc/pagination.tpl' pagination=$pagination cParam_arr=['cSuche'=>$cSuche]}
                    <div class=" block clearall">
                        <div class="right">
                            <form name="bestellungen" method="post" action="bestellungen.php">
                                {$jtl_token}
                                <input type="hidden" name="Suche" value="1" />
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="orderSearch">{__('orderSearchItem')}:</label>
                                    </span>
                                    <input class="form-control" name="cSuche" type="text" value="{if isset($cSuche)}{$cSuche}{/if}" id="orderSearch" />
                                    <button name="submitSuche" type="submit" class="btn btn-primary"><i class="fal fa-search"></i> {__('confSearch')}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <table class="list table table-striped">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{__('orderNumber')}</th>
                            <th class="tleft">{__('customer')}</th>
                            <th class="tleft">{__('orderCostumerRegistered')}</th>
                            <th class="tleft">{__('orderShippingName')}</th>
                            <th class="tleft">{__('orderPaymentName')}</th>
                            <th>{__('orderWawiPickedUp')}</th>
                            <th>{__('status')}</th>
                            <th>{__('orderSum')}</th>
                            <th class="tright">{__('orderDate')}</th>
                            <th class="tright">{__('orderIpAddress')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oBestellung_arr as $oBestellung}
                            <tr>
                                <td class="check">{if $oBestellung->cAbgeholt === 'Y' && $oBestellung->cZahlungsartName !== 'Amazon Payment' && $oBestellung->oKunde !== null}
                                    <input type="checkbox" name="kBestellung[]" value="{$oBestellung->kBestellung}" />{/if}
                                </td>
                                <td>{$oBestellung->cBestellNr}</td>
                                <td>
                                    {if isset($oBestellung->oKunde->cVorname) || isset($oBestellung->oKunde->cNachname) || isset($oBestellung->oKunde->cFirma)}
                                        {$oBestellung->oKunde->cVorname} {$oBestellung->oKunde->cNachname}
                                        {if isset($oBestellung->oKunde->cFirma) && $oBestellung->oKunde->cFirma|strlen > 0}
                                            ({$oBestellung->oKunde->cFirma})
                                        {/if}
                                    {else}
                                        {__('noAccount')}
                                    {/if}
                                </td>
                                <td>{if isset($oBestellung->oKunde) && $oBestellung->oKunde->nRegistriert === 1}{__('yes')}{else}{__('no')}{/if}</td>
                                <td>{$oBestellung->cVersandartName}</td>
                                <td>{$oBestellung->cZahlungsartName}</td>
                                <td class="tcenter">{if $oBestellung->cAbgeholt === 'Y'}{__('yes')}{else}{__('no')}{/if}</td>
                                <td class="tcenter">
                                    {if $oBestellung->cStatus == 1}
                                        {__('new')}
                                    {elseif $oBestellung->cStatus == 2}
                                        {__('orderInProgress')}
                                    {elseif $oBestellung->cStatus == 3}
                                        {__('orderPayed')}
                                    {elseif $oBestellung->cStatus == 4}
                                        {__('orderShipped')}
                                    {elseif $oBestellung->cStatus == 5}
                                        {__('orderPartlyShipped')}
                                    {elseif $oBestellung->cStatus == -1}
                                        {__('orderCanceled')}
                                    {/if}
                                </td>
                                <td class="tcenter">{$oBestellung->WarensummeLocalized[0]}</td>
                                <td class="tright">{$oBestellung->dErstelldatum_de}</td>
                                <td class="tright">{$oBestellung->cIP}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="float-left">
                        <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                        <label for="ALLMSGS">{__('globalSelectAll')}</label>
                    </div>
                    <button name="zuruecksetzenBTN" type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> {__('orderPickedUpResetBTN')}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noDataAvailable')}</div>
    {/if}
</div>
