{include file='tpl_inc/seite_header.tpl' cTitel=#coupons# cDokuURL=#couponsURL#}
<div id="content" class="container-fluid">
    {if $kupons_aktiv|@count > 0}
        <form method="post" action="kupons.php">
            {$jtl_token}
            <input type="hidden" name="del_aktive_kupons" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#activeCoupons#}</h3>
                </div>
                <div class="panel-body">
                    <div class=" block clearall">
                        <div class="left">
                            {if isset($cSuche) && $cSuche|count_characters > 0}
                                {assign var=pAdditional value="&cSuche="|cat:$cSuche}
                            {else}
                                {assign var=pAdditional value=''}
                            {/if}
                            {include file='pagination.tpl' cSite=1 cUrl='kupons.php' oBlaetterNavi=$oBlaetterNaviAktiv cParams=$pAdditional hash=''}
                        </div>
                    </div>
                </div>
                <table class="list table">
                    <thead>
                    <tr>
                        <th class="check"></th>
                        <th class="tleft">{#name#}</th>
                        <th class="tleft">{#value#}</th>
                        <th class="tleft">{#code#}</th>
                        <th class="th-4">{#mbw#}</th>
                        <th class="th-5">{#curmaxusage#}</th>
                        <th class="th-6">{#customerGroup#}</th>
                        <th class="th-7">{#restrictions#}</th>
                        <th class="th-8">{#validity#}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=aktivekupons from=$kupons_aktiv item=kupon_aktiv}
                        <tr>
                            <td class="check"><input id="kupon-{$smarty.foreach.aktivekupons.index}" name="kKupon[]" type="checkbox" value="{$kupon_aktiv->kKupon}" /></td>
                            <td class="TD1"><label for="kupon-{$smarty.foreach.aktivekupons.index}">{$kupon_aktiv->cName}</label></td>
                            <td class="TD2">{if $kupon_aktiv->cWertTyp == "prozent"}{$kupon_aktiv->fWert} %{else}{getCurrencyConversionSmarty fPreisBrutto=$kupon_aktiv->fWert}{/if}</td>
                            <td class="TD3">{$kupon_aktiv->cCode}</td>
                            <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$kupon_aktiv->fMindestbestellwert}</td>
                            <td class="tcenter">{$kupon_aktiv->VerwendungenBisher}/{$kupon_aktiv->Verwendungen}</td>
                            <td class="tcenter">{$kupon_aktiv->Kundengruppe}</td>
                            <td class="tcenter">{$kupon_aktiv->Artikel}</td>
                            <td class="tcenter">{$kupon_aktiv->Gueltigkeit}</td>
                            <td>
                                <a href="kupons.php?kKupon={$kupon_aktiv->kKupon}&token={$smarty.session.jtl_token}" class="btn btn-default" title="bearbeiten"><i class="fa fa-edit"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                    <tfoot>
                    <tr>
                        <td class="check">
                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
                        <td colspan="8" align="left"><label for="ALLMSGS">{#globalSelectAll#}</label></td>
                    </tr>
                    </tfoot>
                </table>
                <div class="panel-footer">
                    <button name="kuponLoeschBTN" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                </div>
            </div>
        </form>
    {/if}

    {if $kupons_inaktiv|@count > 0}
        <div class=" block clearall">
            <div class="left">
                {if isset($cSuche) && $cSuche|count_characters > 0}
                    {assign var=pAdditional value="&cSuche="|cat:$cSuche}
                {else}
                    {assign var=pAdditional value=''}
                {/if}
                {include file='pagination.tpl' cSite=2 cUrl='kupons.php' oBlaetterNavi=$oBlaetterNaviInaktiv cParams=$pAdditional hash=''}
            </div>
        </div>
        <form method="post" action="kupons.php">
            {$jtl_token}
            <input type="hidden" name="del_inaktive_kupons" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#inactiveCoupons#}</h3>
                </div>
                <table class="list table">
                    <thead>
                    <tr>
                        <th class="check"></th>
                        <th class="tleft">{#name#}</th>
                        <th class="tleft">{#value#}</th>
                        <th class="tleft">{#code#}</th>
                        <th class="th-4">{#mbw#}</th>
                        <th class="th-5">{#curmaxusage#}</th>
                        <th class="th-6">{#customerGroup#}</th>
                        <th class="th-7">{#restrictions#}</th>
                        <th class="th-8">{#validity#}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=inaktivekupons from=$kupons_inaktiv item=kupon_inaktiv}
                        <tr>
                            <td class="check"><input name="kKupon[]" type="checkbox" value="{$kupon_inaktiv->kKupon}" />
                            </td>
                            <td class="TD1">{$kupon_inaktiv->cName}</td>
                            <td class="TD2">{if $kupon_inaktiv->cWertTyp == "prozent"}{$kupon_inaktiv->fWert} %{else}{getCurrencyConversionSmarty fPreisBrutto=$kupon_inaktiv->fWert}{/if}</td>
                            <td class="TD3">{$kupon_inaktiv->cCode}</td>
                            <td class="tcenter">{getCurrencyConversionSmarty fPreisBrutto=$kupon_inaktiv->fMindestbestellwert}</td>
                            <td class="tcenter">{$kupon_inaktiv->VerwendungenBisher}/{$kupon_inaktiv->Verwendungen}</td>
                            <td class="tcenter">{$kupon_inaktiv->Kundengruppe}</td>
                            <td class="tcenter">{$kupon_inaktiv->Artikel}</td>
                            <td class="tcenter">{$kupon_inaktiv->Gueltigkeit}</td>
                            <td><a href="kupons.php?kKupon={$kupon_inaktiv->kKupon}&token={$smarty.session.jtl_token}" class="btn btn-default" title="bearbeiten"><i class="fa fa-edit"></i></a>
                            </td>
                        </tr>
                    {/foreach}
                    <tr>
                        <td class="check">
                            <input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                        </td>
                        <td colspan="8" align="left"><label for="ALLMSGS2">{#globalSelectAll#}</label></td>
                    </tr>
                    </tbody>
                </table>
                <div class="panel-footer">
                    <button class="btn btn-danger" name="kuponLoeschBTN" type="submit" value="{#delete#}"><i class="fa fa-trash"></i> Markierte l&ouml;schen</button>
                </div>
            </div>
        </form>
    {/if}

    <form name="kupon_erstellen" method="post" action="kupons.php">
        {$jtl_token}
        <input type="hidden" name="neu" value="1" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#newCoupon#}</h3>
            </div>
            <table class="list table">
                <tbody>
                <tr>
                    <td>
                        <input class="checkfield" type="radio" id="cKuponTyp" name="cKuponTyp" value="standard" checked="checked" />
                        <label for="cKuponTyp">{#standardCoupon#}</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input class="checkfield" type="radio" id="cKuponTyp1" name="cKuponTyp" value="versandkupon" />
                        <label for="cKuponTyp1">{#shippingCoupon#}</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input class="checkfield" type="radio" id="cKuponTyp2" name="cKuponTyp" value="neukundenkupon" />
                        <label for="cKuponTyp2">{#newCustomerCoupon#}</label>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="panel-footer">
                <button type="submit" value="{#newCoupon#}" class="btn btn-primary"><i class="fa fa-share"></i> {#newCoupon#}</button>
            </div>
        </div>
    </form>
</div>