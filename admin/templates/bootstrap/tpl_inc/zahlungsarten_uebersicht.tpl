{include file='tpl_inc/seite_header.tpl' cTitel=__('paymentmethods') cBeschreibung=__('installedPaymentmethods') cDokuURL=__('paymentmethodsURL')}
<div id="content" class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-content-center">
                    <thead>
                    <tr>
                        <th>{__('installedPaymentTypes')}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $zahlungsarten as $zahlungsart}
                        <tr class="text-vcenter">
                            <td>
                                {if $zahlungsart->nActive == 1}
                                    <span class="text-success" title="{__('active')}"><i class="fal fa-check text-success"></i></span>
                                {else}
                                    <span class="text-danger" title="{__('inactive')}">
                                        <i class="fa fa-exclamation-triangle"></i>
                                    </span>
                                {/if}
                                <span class="ml-2">{$zahlungsart->cName}
                                    <small>{$zahlungsart->cAnbieter}</small>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group" role="group">
                                    <a href="zahlungsarten.php?a=log&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                       class="btn btn-sm down
                                                  {if $zahlungsart->nLogCount > 0}
                                                        {if $zahlungsart->nErrorLogCount}btn-danger{else}btn-default{/if}
                                                  {else}
                                                        btn-default disabled
                                                  {/if}"
                                       title="{__('viewLog')}">
                                        <i class="fa
                                                      {if $zahlungsart->nLogCount > 0}
                                                            {if $zahlungsart->nErrorLogCount}fa-warning{else}fa-bars{/if}
                                                      {else}
                                                            fa-check
                                                      {/if}"></i>
                                    </a>
                                    <a {if $zahlungsart->nEingangAnzahl > 0}href="zahlungsarten.php?a=payments&kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"{/if}
                                       class="btn btn-default {if $zahlungsart->nEingangAnzahl === 0}disabled{/if}"
                                       title="Zahlungseingänge">
                                        <i class="fa fa-money"></i>
                                    </a>
                                    <a href="zahlungsarten.php?kZahlungsart={$zahlungsart->kZahlungsart}&token={$smarty.session.jtl_token}"
                                       class="btn btn-default btn-sm" title="{__('edit')}">
                                        <i class="fal fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <form method="post" action="zahlungsarten.php" class="top" style="margin-bottom: 15px;">
            {$jtl_token}
            <input type="hidden" name="checkNutzbar" value="1"/>
            <button name="checkSubmit" type="submit" value="{__('paymentmethodsCheckAll')}" class="btn btn-info button">
                <i class="fa fa-refresh"></i> {__('paymentmethodsCheckAll')}</button>
        </form>
    </div>
    <div class="col-md-5">
        {*<div class="card">*}
            {*<div class="card-body">*}
                {*<div class="table-responsive">*}
                    {*<table class="table">*}
                        {*<thead>*}
                        {*<tr>*}
                            {*<th colspan="2">Wir empfehlen:</th>*}
                        {*</tr>*}
                        {*</thead>*}
                        {*<tbody>*}
                        {*<tr>*}
                            {*<td><img src="placeholder/klarna-logo.png" width="108" height="42" alt="Klarna"></td>*}
                            {*<td>*}
                                {*<p>Klarna wurde 2005 in Stockholm mit der Idee gegründet, das Einkaufen zu*}
                                    {*vereinfachen. Dies erreichen wir, indem wir es den Verbrauchern ermöglichen,*}
                                    {*erst nach Warenerhalt zu bezahlen, und gleichzeitig das Kredit- und*}
                                    {*Betrugsrisiko für die Händler übernehmen.</p>*}
                                {*<a href="#" class="btn btn-primary">Mehr erfahren</a>*}
                            {*</td>*}
                        {*</tr>*}
                        {*<tr>*}
                            {*<td><img src="placeholder/klarna-logo.png" width="108" height="42" alt="Klarna"></td>*}
                            {*<td>*}
                                {*<p>Klarna wurde 2005 in Stockholm mit der Idee gegründet, das Einkaufen zu*}
                                    {*vereinfachen. Dies erreichen wir, indem wir es den Verbrauchern ermöglichen,*}
                                    {*erst nach Warenerhalt zu bezahlen, und gleichzeitig das Kredit- und*}
                                    {*Betrugsrisiko für die Händler übernehmen.</p>*}
                                {*<a href="#" class="btn btn-primary">Mehr erfahren</a>*}
                            {*</td>*}
                        {*</tr>*}
                        {*<tr>*}
                            {*<td><img src="placeholder/klarna-logo.png" width="108" height="42" alt="Klarna"></td>*}
                            {*<td>*}
                                {*<p>Klarna wurde 2005 in Stockholm mit der Idee gegründet, das Einkaufen zu*}
                                    {*vereinfachen. Dies erreichen wir, indem wir es den Verbrauchern ermöglichen,*}
                                    {*erst nach Warenerhalt zu bezahlen, und gleichzeitig das Kredit- und*}
                                    {*Betrugsrisiko für die Händler übernehmen.</p>*}
                                {*<a href="#" class="btn btn-primary">Mehr erfahren</a>*}
                            {*</td>*}
                        {*</tr>*}
                        {*</tbody>*}
                    {*</table>*}
                {*</div>*}
            {*</div>*}
        </div>
    </div>
</div>