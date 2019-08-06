<div id="page">
    <div id="content">
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{$oUmfrageStats->cName}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <strong>{__('umfrageValidation')}:</strong>
                    </div>
                    <div class="col-auto">
                        {$oUmfrageStats->dGueltigVon_de} - {if $oUmfrageStats->dGueltigBis === null}{__('umfrageInfinite')}{else}{$oUmfrageStats->dGueltigBis_de}{/if}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>{__('customerGroup')}:</strong>
                    </div>
                    <div class="col-auto">
                        {foreach $oUmfrageStats->cKundengruppe_arr as $cKundengruppe}
                            {$cKundengruppe}{if !$cKundengruppe@last},{/if}
                        {/foreach}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>{__('active')}:</strong>
                    </div>
                    <div class="col-auto">
                        {$oUmfrageStats->nAktiv}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>{__('umfrageTryCount')}:</strong>
                    </div>
                    <div class="col-auto">
                        {$oUmfrageStats->nAnzahlDurchfuehrung}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>{__('description')}:</strong>
                    </div>
                    <div class="col-auto">
                        {$oUmfrageStats->cBeschreibung}
                    </div>
                </div>
            </div>
        </div>
        {if isset($oUmfrageStats->oUmfrageFrage_arr) && $oUmfrageStats->oUmfrageFrage_arr|@count > 0}
            <div>
                {foreach $oUmfrageStats->oUmfrageFrage_arr as $oUmfrageFrage}
                    {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr) && $oUmfrageFrage->oUmfrageFrageAntwort_arr|@count > 0}
                        {if $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::MATRIX_SINGLE
                        || $oUmfrageFrage->cTyp === \JTL\Survey\QuestionType::MATRIX_MULTI}
                            <div class="card">
                                <div class="card-header">
                                    <div class="subheading1">
                                        <strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="payment">
                                        <div id="tabellenLivesuche" class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th class="th-1" style="width: 5%;">{__('umfrageQASing')}</th>
                                                        {foreach $oUmfrageFrage->oUmfrageMatrixOption_arr as $oUmfrageMatrixOption}
                                                            {assign var=maxbreite value=95}
                                                            {assign var=anzahloption value=$oUmfrageFrage->oUmfrageMatrixOption_arr|@count}
                                                            {math equation="x/y" x=$maxbreite y=$anzahloption assign=breite}
                                                            <th class="th-1 text-center" style="width: {$breite}%;">{$oUmfrageMatrixOption->cName}</th>
                                                        {/foreach}
                                                    </tr>
                                                </thead>
                                                {if isset($oUmfrageFrage->oUmfrageFrageAntwort_arr)}
                                                    <tbody>
                                                    {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                                                        {assign var=kUmfrageFrageAntwort value=$oUmfrageFrageAntwort->kUmfrageFrageAntwort}
                                                        <tr>
                                                            <td>{$oUmfrageFrageAntwort->cName}</td>
                                                            {foreach $oUmfrageFrage->oUmfrageMatrixOption_arr as $oUmfrageMatrixOption}
                                                                {assign var=kUmfrageMatrixOption value=$oUmfrageMatrixOption->kUmfrageMatrixOption}
                                                                <td class="min-w-sm text-center">
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
                                                    </tbody>
                                                {/if}
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="card">
                                <div class="card-header">
                                    <div class="subheading1">
                                        <strong>{$oUmfrageFrage->cName}</strong> - {$oUmfrageFrage->cTypMapped}
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="payment">
                                        <div id="tabellenLivesuche">
                                            <table class="table table-responsive table-striped">
                                                <thead>
                                                    <tr>
                                                        <th class="th-1" style="width: 20%;">{__('umfrageQASing')}</th>
                                                        <th class="th-2" style="width: 60%;"></th>
                                                        <th class="th-3 text-center" style="width: 10%;">{__('umfrageQResPercent')}</th>
                                                        <th class="th-4 text-center" style="width: 10%;">{__('umfrageQResCount')}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                                                    <tr>
                                                        <td style="width: 20%;">{$oUmfrageFrageAntwort->cName}</td>
                                                        <td style="width: 60%;">
                                                            <div class="freqbar" style="width: {$oUmfrageFrageAntwort->fProzent}%; height: 10px;"></div>
                                                        </td>
                                                        <td class="text-center" style="width: 10%;">
                                                            {if $oUmfrageFrageAntwort@first}
                                                                <strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
                                                            {elseif $oUmfrageFrageAntwort->nAnzahlAntwort == $oUmfrageFrage->oUmfrageFrageAntwort_arr[0]->nAnzahlAntwort}
                                                                <strong>{$oUmfrageFrageAntwort->fProzent} %</strong>
                                                            {else}
                                                                {$oUmfrageFrageAntwort->fProzent} %
                                                            {/if}
                                                        </td>
                                                        <td class="text-center" style="width: 10%;">{$oUmfrageFrageAntwort->nAnzahlAntwort}</td>
                                                    </tr>
                                                {/foreach}
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td style="width: 20%;"></td>
                                                        <td style="width: 60%;"></td>
                                                        <td class="text-center" style="width: 10%;">{__('umfrageQMax')}</td>
                                                        <td class="text-center" style="width: 10%;">{$oUmfrageFrage->nAnzahlAntworten}</td>
                                                    </tr>
                                                </tfoot>
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
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <a class="btn btn-outline-primary btn-block" href="umfrage.php">
                        {__('goBack')}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>