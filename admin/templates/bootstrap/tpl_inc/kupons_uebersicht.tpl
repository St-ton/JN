{include file='tpl_inc/seite_header.tpl' cTitel=#coupons# cBeschreibung=#couponsDesc# cDokuURL=#couponsURL#}

<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab">
            <a data-toggle="tab" role="tab" href="#standard" aria-expanded="true">{#standardCoupon#}s</a>
        </li>
        <li class="tab">
            <a data-toggle="tab" role="tab" href="#versandkupon" aria-expanded="true">{#shippingCoupon#}s</a>
        </li>
        <li class="tab">
            <a data-toggle="tab" role="tab" href="#neukundenkupon" aria-expanded="true">{#newCustomerCoupon#}s</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="standard" class="tab-pane fade in">
            Hello
        </div>
    </div>
    <div class="tab-content">
        <div id="standard" class="tab-pane fade in">
            Hello
        </div>
    </div>
    <form method="post" action="kupons.php">
        {$jtl_token}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#activeCoupons#}</h3>
            </div>
            <table class="list table">
                <thead>
                    <tr>
                        <th></th>
                        <th>{#name#}</th>
                        <th>{#value#}</th>
                        <th>{#code#}</th>
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
                            <td>
                                {if $oKupon->cWertTyp == 'festpreis'}
                                    {getCurrencyConversionSmarty fPreisBrutto=$oKupon->fWert}
                                {else}
                                    {$oKupon->fWert} %
                                {/if}
                            </td>
                            <td>{$oKupon->cCode}</td>
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
                <button type="submit" class="btn btn-danger" name="action" value="loeschen"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
            </div>
        </div>
    </form>
    <form method="post" action="kupons.php">
        {$jtl_token}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#newCoupon#}</h3>
            </div>
            <table class="list table">
                <tbody>
                    <tr>
                        <td>
                            <input type="radio" class="checkfield" id="cKuponTyp1" name="cKuponTyp" value="standard" checked="checked">
                            <label for="cKuponTyp1">{#standardCoupon#}</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" class="checkfield" id="cKuponTyp2" name="cKuponTyp" value="versandkupon">
                            <label for="cKuponTyp2">{#shippingCoupon#}</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" class="checkfield" id="cKuponTyp3" name="cKuponTyp" value="neukundenkupon">
                            <label for="cKuponTyp3">{#newCustomerCoupon#}</label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary" name="action" value="erstellen"><i class="fa fa-share"></i> {#newCoupon#}</button>
            </div>
        </div>
    </form>
</div>