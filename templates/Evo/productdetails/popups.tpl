{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{assign var=kArtikel value=$Artikel->kArtikel}
{if $Artikel->kArtikelVariKombi > 0}
    {assign var=kArtikel value=$Artikel->kArtikelVariKombi}
{/if}
{if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P'}
    <div id="popupz{$kArtikel}" class="hidden">
        {include file='productdetails/question_on_item.tpl'}
    </div>
{/if}

{if ($verfuegbarkeitsBenachrichtigung == 2 || $verfuegbarkeitsBenachrichtigung == 3) && $Artikel->cLagerBeachten === 'Y'}
    <div id="popupn{$kArtikel}" class="hidden">
        {include file='productdetails/availability_notification_form.tpl' tplscope='artikeldetails'}
    </div>
{/if}

{nocache}
{if isset($bWarenkorbHinzugefuegt) && $bWarenkorbHinzugefuegt}
    {if !isset($kArtikel)}
        {assign var=kArtikel value=$Artikel->kArtikel}
        {if $Artikel->kArtikelVariKombi > 0}
            {assign var=kArtikel value=$Artikel->kArtikelVariKombi}
        {/if}
    {/if}
    <div id="popupa{$kArtikel}" class="hidden">
        {include file='productdetails/pushed.tpl' oArtikel=$Artikel fAnzahl=$bWarenkorbAnzahl}
    </div>
{/if}
{/nocache}
<script type="text/javascript">
    $(function() {
        {if isset($fehlendeAngaben_benachrichtigung) && count($fehlendeAngaben_benachrichtigung) > 0 && ($verfuegbarkeitsBenachrichtigung == 2 || $verfuegbarkeitsBenachrichtigung == 3) && $Artikel->cLagerBeachten === 'Y'}
            show_popup('n{$kArtikel}');
        {/if}

        {if isset($fehlendeAngaben_fragezumprodukt) && $fehlendeAngaben_fragezumprodukt|@count > 0 && $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P'}
            show_popup('z{$kArtikel}');
        {/if}
    });

    function show_popup(item) {ldelim}
        var html = $('#popup' + item).html();
        var title = $(html).find('h3').text();
        eModal.alert({
            message: html,
            title: title
        });
    {rdelim}
</script>
