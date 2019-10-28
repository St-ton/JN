<div id="page">
    <div id="content">
        <div id="welcome" class="post">
            <h2 class="title"><span>{__('poll')}</span></h2>
            <div class="content">
                <p>{__('umfrageDesc')}</p>
            </div>
        </div>

        {if $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0 && $oUmfrageFrage->oUmfrageFrageAntwort_arr}
            <form method="post" action="umfrage.php">
                {$jtl_token}
                <input type="hidden" name="umfrage" value="1" />
                <input name="umfrage_statistik" type="hidden" value="1" />
                <input name="kUmfrage" type="hidden" value="{$oUmfrageFrage->kUmfrage}" />
                <p style="width: 55px; border-bottom: 1px solid #000000;"><b>{__('umfrageQ')}:</b></p>

                <div id="payment">
                    <div id="tabellenLivesuche" class="table-responsive">
                        <b>{$oUmfrageFrage->cName}</b><br /><br />
                        <table class="table table-striped">
                            <tr>
                                <th class="th-1" style="width: 20%;">{__('umfrageQASing')}</th>
                                <th class="th-2" style="width: 60%;"></th>
                                <th class="th-3" style="width: 10%;">{__('umfrageQResPercent')}</th>
                                <th class="th-4" style="width: 10%;">{__('umfrageQResCount')}</th>
                            </tr>
                        {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                            <tr>
                                <td style="width: 20%;">{$oUmfrageFrageAntwort->cName}</td>
                                <td style="width: 60%;"><div class="freqbar" style="width: {$oUmfrageFrageAntwort->nProzent}%; height: 10px;"></div></td>
                                <td style="width: 10%;">
                                {if $oUmfrageFrageAntwort@first}
                                    <b>{$oUmfrageFrageAntwort->nProzent} %</b>
                                {elseif $oUmfrageFrageAntwort->nAnzahlAntwort == $oUmfrageFrage->oUmfrageFrageAntwort_arr[0]->nAnzahlAntwort}
                                    <b>{$oUmfrageFrageAntwort->nProzent} %</b>
                                {else}
                                    {$oUmfrageFrageAntwort->nProzent} %
                                {/if}
                                </td>
                                <td style="width: 10%;">{$oUmfrageFrageAntwort->nAnzahlAntwort}</td>
                            </tr>
                            {if $oUmfrageFrageAntwort@last}
                            <tr>
                                <td></td>
                                <td colspan="2" align="right">{__('umfrageQMax')}</td>
                                <td align="center">{$oUmfrageFrage->nMaxAntworten}</td>
                            </tr>
                            {/if}
                        {/foreach}
                    </table>
                    </div>
                </div>

                <p><input name="zurueck" type="submit" value="{__('goBack')}" /></p>
            </form>
        {/if}
    </div>
</div>