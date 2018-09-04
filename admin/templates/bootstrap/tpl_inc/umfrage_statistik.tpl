<div id="page">
    <div id="content" class="container-fluid">
        <h2 class="txtBlack">{$oUmfrageStats->cName}</h2>
        <div class="row">
            <div class="col-md-3">
                <strong>{#umfrageValidation#}:</strong><br/>
                {$oUmfrageStats->dGueltigVon_de}<br/>
                -{if $oUmfrageStats->dGueltigBis === null}{#umfrageInfinite#}{else}{$oUmfrageStats->dGueltigBis_de}{/if}
            </div>
            <div class="col-md-3">
                <strong>{#umfrageCustomerGrp#}:</strong><br/>
                {foreach $oUmfrageStats->cKundengruppe_arr as $cKundengruppe}
                    {$cKundengruppe}{if !$cKundengruppe@last},{/if}
                {/foreach}
            </div>
            <div class="col-md-3">
                <strong>{#umfrageActive#}:</strong><br/>
                {$oUmfrageStats->nAktiv}
            </div>
            <div class="col-md-3">
                <strong>{#umfrageTryCount#}:</strong><br/>
                {$oUmfrageStats->nAnzahlDurchfuehrung}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <strong>{#umfrageText#}:</strong><br/>
                {$oUmfrageStats->cBeschreibung}
            </div>
        </div>

        {if isset($oUmfrageStats->oUmfrageFrage_arr) && $oUmfrageStats->oUmfrageFrage_arr|@count > 0}
            <div>
                <h3>{#umfrageQ#}:</h3>
                {foreach $oUmfrageStats->oUmfrageFrage_arr as $oUmfrageFrage}
                    {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
                        {if $oUmfrageFrage->cTyp === \Survey\QuestionType::MATRIX_SINGLE
                        || $oUmfrageFrage->cTyp === \Survey\QuestionType::MATRIX_MULTI}
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}
                                </div>
                                <div class="panel-body">
                                    <div id="payment">
                                        <div id="tabellenLivesuche" class="table-responsive">
                                            <table class="table table-striped">
                                                <tr>
                                                    <th class="th-1" style="width: 5%;">{#umfrageQASing#}</th>
                                                    {foreach $oUmfrageFrage->oUmfrageMatrixOption_arr as $oUmfrageMatrixOption}
                                                        {assign var=maxbreite value=95}
                                                        {assign var=anzahloption value=$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}
                                                        {math equation="x/y" x=$maxbreite y=$anzahloption assign=breite}
                                                        <th class="th-1" style="width: {$breite}%;">{$oUmfrageMatrixOption->cName}</th>
                                                    {/foreach}
                                                </tr>
                                                {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr)}
                                                    {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                                                        {assign var=kUmfrageFrageAntwort value=$oUmfrageFrageAntwort->kUmfrageFrageAntwort}
                                                        <tr>
                                                            <td>{$oUmfrageFrageAntwort->cName}</td>
                                                            {foreach $oUmfrageFrage->oUmfrageMatrixOption_arr as $oUmfrageMatrixOption}
                                                                {assign var=kUmfrageMatrixOption value=$oUmfrageMatrixOption->kUmfrageMatrixOption}
                                                                <td align="center">
                                                                    {if $oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nBold == 1}
                                                                    <strong>{/if}
                                                                        {$oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->fProzent}
                                                                        %
                                                                        ({$oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nAnzahl}
                                                                        )
                                                                        {if $oUmfrageFrage->oErgebnisMatrix_arr[$kUmfrageFrageAntwort][$kUmfrageMatrixOption]->nBold == 1}</strong>{/if}
                                                                </td>
                                                            {/foreach}
                                                        </tr>
                                                    {/foreach}
                                                {/if}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}
                                </div>
                                <div class="panel-body">
                                    <div id="payment">
                                        <div id="tabellenLivesuche">
                                            <table class="table table-responsive table-striped">
                                                <tr>
                                                    <th class="th-1" style="width: 20%;">{#umfrageQASing#}</th>
                                                    <th class="th-2" style="width: 60%;"></th>
                                                    <th class="th-3" style="width: 10%;">{#umfrageQResPercent#}</th>
                                                    <th class="th-4" style="width: 10%;">{#umfrageQResCount#}</th>
                                                </tr>
                                                {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                                                    <tr>
                                                        <td style="width: 20%;">{$oUmfrageFrageAntwort->cName}</td>
                                                        <td style="width: 60%;">
                                                            <div class="freqbar" style="width: {$oUmfrageFrageAntwort->fProzent}%; height: 10px;"></div>
                                                        </td>
                                                        <td style="width: 10%;">
                                                            {if $oUmfrageFrageAntwort@first}
                                                                <strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
                                                            {elseif $oUmfrageFrageAntwort->nAnzahlAntwort == $oUmfrageFrage->oUmfrageFrageAntwort_arr[0]->nAnzahlAntwort}
                                                                <strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
                                                            {else}
                                                                {$oUmfrageFrageAntwort->fProzent} %
                                                            {/if}
                                                        </td>
                                                        <td style="width: 10%;">{$oUmfrageFrageAntwort->nAnzahlAntwort}</td>
                                                    </tr>
                                                    {if $oUmfrageFrageAntwort@last}
                                                        <tr>
                                                            <td></td>
                                                            <td colspan="2" align="right">{#umfrageQMax#}</td>
                                                            <td align="center">{$oUmfrageFrage->nAnzahlAntworten}</td>
                                                        </tr>
                                                    {/if}
                                                {/foreach}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/if}
                {/foreach}
            </div>
        {/if}
    </div>
</div>