<div id="page">
    <div id="content" class="container-fluid">
        <h2 class="txtBlack">{$oUmfrage->cName}</h2>
        <div class="row">
            <div class="col-md-3">
                <strong>{__('umfrageValidation')}:</strong><br/>
                {$oUmfrage->dGueltigVon_de}<br/>
                -{if $oUmfrage->dGueltigBis === null}{__('umfrageInfinite')}{else}{$oUmfrage->dGueltigBis_de}{/if}
            </div>
            <div class="col-md-3">
                <strong>{__('umfrageCustomerGrp')}:</strong><br/>
                {foreach name=kundengruppen from=$oUmfrage->cKundengruppe_arr item=cKundengruppe}
                    {$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
                {/foreach}
            </div>
            <div class="col-md-3">
                <strong>{__('umfrageActive')}:</strong><br/>
                {$oUmfrage->nAktiv}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <strong>{__('umfrageText')}:</strong><br/>
                {$oUmfrage->cBeschreibung}
            </div>
        </div>
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

        {if $oUmfrage->oUmfrageFrage_arr|@count > 0 && $oUmfrage->oUmfrageFrage_arr}
        <form method="post" action="umfrage.php">
            {$jtl_token}
            <input type="hidden" name="umfrage" value="1" />
            <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
            <input type="hidden" name="umfrage_frage_loeschen" value="1" />
            <br />
            <p><strong>{__('umfrageQs')}:</strong></p>
            {foreach name=umfragefrage from=$oUmfrage->oUmfrageFrage_arr item=oUmfrageFrage}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>{$smarty.foreach.umfragefrage.iteration}.</strong>
                        <input id="question-{$smarty.foreach.umfragefrage.iteration}" name="kUmfrageFrage[]" type="checkbox" value="{$oUmfrageFrage->kUmfrageFrage}">
                        <label for="question-{$smarty.foreach.umfragefrage.iteration}">{$oUmfrageFrage->cName}</label> [<a href="umfrage.php?umfrage=1&kUmfrage={$oUmfrage->kUmfrage}&kUmfrageFrage={$oUmfrageFrage->kUmfrageFrage}&fe=1&token={$smarty.session.jtl_token}">{__('umfrageEdit')}</a>]
                    </div>
                    <div class="panel-body">
                        <strong>{$oUmfrageFrage->cTypMapped}: </strong>
                        {$oUmfrageFrage->cBeschreibung}

                        {if $oUmfrageFrage->cTyp !== \Survey\QuestionType::TEXT_STATIC && $oUmfrageFrage->cTyp !== \Survey\QuestionType::TEXT_PAGE_CHANGE && $oUmfrageFrage->cTyp !== \Survey\QuestionType::TEXT_SMALL && $oUmfrageFrage->cTyp !== \Survey\QuestionType::TEXT_BIG}
                            <hr/>
                            <div class="row">
                                <div class="col-md-{if $oUmfrageFrage->oUmfrageMatrixOption_arr|@count > 0}4{else}8{/if} col-md-offset-1">
                                    <strong>{__('umfrageQA')}:</strong>
                                    <table  class="table">
                                        {foreach name=umfragefrageantwort from=$oUmfrageFrage->oUmfrageFrageAntwort_arr item=oUmfrageFrageAntwort}
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
                                            {foreach name=umfragemaxtrixoption from=$oUmfrageFrage->oUmfrageMatrixOption_arr item=oUmfrageMatrixOption}
                                                <tr>
                                                    <td style="width: 10px;"><input id="answ-{$smarty.foreach.umfragemaxtrixoption.index}" name="kUmfrageMatrixOption[]" type="checkbox" value="{$oUmfrageMatrixOption->kUmfrageMatrixOption}"></td>
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
                <p class="btn-group">
                    <a class="btn btn-default" href="umfrage.php"><i class="fa fa-angle-double-left"></i> {__('umfrageBack')}</a>
                    <button class="btn btn-danger" name="umfragefrageloeschen" type="submit" value="{__('delete')}"><i class="fa fa-trash"></i> {__('delete')}</button>
                </p>
            </form>
        {else}
            <a class="btn btn-default" href="umfrage.php"><i class="fa fa-angle-double-left"></i> {__('umfrageBack')}</a>
        {/if}
    </div>
</div>