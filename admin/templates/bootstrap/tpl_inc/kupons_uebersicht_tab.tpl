
<div id="{$cKuponTyp}" class="tab-pane fade{if $tab == $cKuponTyp} active in{/if}">
    <form method="post" action="kupons.php">
        {$jtl_token}
        <input type="hidden" name="cKuponTyp" id="cKuponTyp" value="{$cKuponTyp}">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Alle {$cKuponTypName}s</h3>
            </div>
            <table class="list table">
                <thead>
                <tr>
                    <th></th>
                    <th>{#name#}</th>
                    {if $cKuponTyp == 'standard' || $cKuponTyp == 'neukundenkupon'}
                        <th>{#value#}</th>
                    {/if}
                    {if $cKuponTyp == 'standard' || $cKuponTyp == 'versandkupon'}
                        <th>{#code#}</th>
                    {/if}
                    <th>{#mbw#}</th>
                    <th>{#curmaxusage#}</th>
                    <th>{#customerGroup#}</th>
                    <th>{#restrictions#}</th>
                    <th>{#validity#}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $oKupon_arr as $oKupon}
                    <tr>
                        <td><input type="checkbox" name="kKupon_arr[]" id="kupon-{$oKupon->kKupon}" value="{$oKupon->kKupon}"></td>
                        <td><label for="kupon-{$oKupon->kKupon}">{$oKupon->cName}</label></td>
                        {if $cKuponTyp == 'standard' || $cKuponTyp == 'neukundenkupon'}
                            <td>
                                {if $oKupon->cWertTyp == 'festpreis'}
                                    {getCurrencyConversionSmarty fPreisBrutto=$oKupon->fWert}
                                {else}
                                    {$oKupon->fWert} %
                                {/if}
                            </td>
                        {/if}
                        {if $cKuponTyp == 'standard' || $cKuponTyp == 'versandkupon'}
                            <td>{$oKupon->cCode}</td>
                        {/if}
                        <td>{getCurrencyConversionSmarty fPreisBrutto=$oKupon->fMindestbestellwert}</td>
                        <td>{$oKupon->nVerwendungenBisher} von {$oKupon->nVerwendungen}</td>
                        <td>{$oKupon->cKundengruppe}</td>
                        <td>{$oKupon->ArtikelInfo}</td>
                        <td>
                            <strong>von:</strong> {$oKupon->dGueltigAb}<br>
                            <strong>bis:</strong> {$oKupon->dGueltigBis}
                        </td>
                        <td>
                            <button type="submit" class="btn btn-default" name="kKuponBearbeiten" value="{$oKupon->kKupon}">
                                <i class="fa fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
                <tfoot>
                <tr>
                    <td><input type="checkbox" name="ALLMSGS" id="ALLMSGS" onclick="AllMessages(this.form);"></td>
                    <td colspan="8"><label for="ALLMSGS">Alle ausw&auml;hlen</td>
                </tr>
                </tfoot>
            </table>
            <div class="panel-footer">
                <div class="btn-group">
                    <button type="submit" class="btn btn-danger" name="action" value="loeschen"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                    <button type="submit" class="btn btn-primary" name="action" value="erstellen"><i class="fa fa-share"></i> Neuen Kupon erstellen</button>
                </div>
            </div>
        </div>
    </form>
</div>
