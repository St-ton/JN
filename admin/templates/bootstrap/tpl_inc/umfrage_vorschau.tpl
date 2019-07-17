<div id="page">
    <div id="content" class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{$oUmfrage->cName}</div>
                <hr class="mb-n4">
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <strong>{__('umfrageValidation')}:</strong><br/>
                        {$oUmfrage->dGueltigVon_de}
                        - {if $oUmfrage->dGueltigBis === null}{__('umfrageInfinite')}{else}{$oUmfrage->dGueltigBis_de}{/if}
                    </div>
                    <div class="col-md-3">
                        <strong>{__('customerGroup')}:</strong><br/>
                        {$oUmfrage->cKundengruppe_arr|implode:','}
                    </div>
                    <div class="col-md-3">
                        <strong>{__('active')}:</strong><br/>
                        {$oUmfrage->nAktiv}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <strong>{__('description')}:</strong><br/>
                        {$oUmfrage->cBeschreibung}
                    </div>
                </div>
                <hr class="mb-4">
                <div class="btn-group">
                    <br/>
                    <form method="post" action="umfrage.php" class="left">
                        {$jtl_token}
                        <input type="hidden" name="umfrage" value="1" />
                        <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
                        <input type="hidden" name="umfrage_frage_hinzufuegen" value="1" />
                        <button class="btn btn-primary" name="umfragefragehinzufuegen" type="submit" value="{__('umfrageQAdd')}"><i class="fa fa-share"></i> {__('umfrageQAdd')}</button>
                    </form>

                    <form method="post" action="umfrage.php" class="left">
                        {$jtl_token}
                        <input type="hidden" name="umfrage" value="1" />
                        <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
                        <input type="hidden" name="umfrage_statistik" value="1" />
                        <button class="btn btn-default" name="umfragestatistik" type="submit" value="{__('umfrageStatsView')}"><i class="fa fa-bar-chart"></i> {__('umfrageStatsView')}</button>
                    </form>
                </div>
            </div>
        </div>
        {if $oUmfrage->oUmfrageFrage_arr|@count > 0 && $oUmfrage->oUmfrageFrage_arr}
        <form method="post" action="umfrage.php">
            {$jtl_token}
            <input type="hidden" name="umfrage" value="1" />
            <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
            <input type="hidden" name="umfrage_frage_loeschen" value="1" />
            <br />
            <p><strong>{__('umfrageQs')}:</strong></p>
            {foreach $oUmfrage->oUmfrageFrage_arr as $oUmfrageFrage}
                <div class="card">
                    <div class="card-header">
                        <strong>{$oUmfrageFrage@iteration}.</strong>
                        <input id="question-{$oUmfrageFrage@iteration}" name="kUmfrageFrage[]" type="checkbox" value="{$oUmfrageFrage->kUmfrageFrage}">
                        <label for="question-{$oUmfrageFrage@iteration}">{$oUmfrageFrage->cName}</label> [<a href="umfrage.php?umfrage=1&kUmfrage={$oUmfrage->kUmfrage}&kUmfrageFrage={$oUmfrageFrage->kUmfrageFrage}&fe=1&token={$smarty.session.jtl_token}">{__('edit')}</a>]
                    </div>
                    <div class="card-body">
                        <strong>{$oUmfrageFrage->cTypMapped}: </strong>
                        {$oUmfrageFrage->cBeschreibung}

                        {if $oUmfrageFrage->cTyp !== \JTL\Survey\QuestionType::TEXT_STATIC && $oUmfrageFrage->cTyp !== \JTL\Survey\QuestionType::TEXT_PAGE_CHANGE && $oUmfrageFrage->cTyp !== \JTL\Survey\QuestionType::TEXT_SMALL && $oUmfrageFrage->cTyp !== \JTL\Survey\QuestionType::TEXT_BIG}
                            <hr/>
                            <div class="row">
                                <div class="col-md-{if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}4{else}8{/if} col-md-offset-1">
                                    <strong>{__('umfrageQA')}:</strong>
                                    <table  class="table">
                                        {foreach $oUmfrageFrage->oUmfrageFrageAntwort_arr as $oUmfrageFrageAntwort}
                                            <tr>
                                                <td style="width: 10px;"><input name="kUmfrageFrageAntwort[]" type="checkbox" value="{$oUmfrageFrageAntwort->kUmfrageFrageAntwort}"></td>
                                                <td>{$oUmfrageFrageAntwort->cName}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                                {if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0 && $oUmfrageFrage->oUmfrageMatrixOption_arr}
                                    <div class="col-md-4"><strong>{__('umfrageQO')}:</strong>
                                        <table  class="table">
                                            {foreach $oUmfrageFrage->oUmfrageMatrixOption_arr as $oUmfrageMatrixOption}
                                                <tr>
                                                    <td style="width: 10px;"><input id="answ-{$oUmfrageMatrixOption@index}" name="kUmfrageMatrixOption[]" type="checkbox" value="{$oUmfrageMatrixOption->kUmfrageMatrixOption}"></td>
                                                    <td>{$oUmfrageMatrixOption->cName}</td>
                                                </tr>
                                            {/foreach}
                                        </table>
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                </div>
            {/foreach}
                <div class="card-footer save-wrapper">
                    <div class="btn-group">
                        <a class="btn btn-default" href="umfrage.php"><i class="fa fa-angle-double-left"></i> {__('back')}</a>
                        <button class="btn btn-danger" name="umfragefrageloeschen" type="submit" value="{__('delete')}"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                    </div>
                </div>
            </form>
        {else}
            <a class="btn btn-default" href="umfrage.php"><i class="fa fa-angle-double-left"></i> {__('back')}</a>
        {/if}
    </div>
</div>