{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{*<object type="application/x-shockwave-flash" data="{$PFAD_FLASHCHART}open-flash-chart.swf" id="price-chart" width="100%" height="250">*}
    {*<param name="movie" value="{$PFAD_FLASHCHART}open-flash-chart.swf" />*}
    {*<param name="wmode" value="transparent" />*}
    {*<param name="allowscriptaccess" value="always" />*}
    {*<param name="FlashVars" value="data-file={$ShopURL}/includes/preisverlaufgraph_ofc.php?cOption={if isset($Artikel->kVariKindArtikel)}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if};{$Artikel->Preise->kKundengruppe};{$Artikel->kSteuerklasse};{$Artikel->fMwSt}">*}
{*</object>*}
{*<div id="chart_wrapper"></div>*}

<canvas id="priceHistoryChart" width="400" height="150"></canvas>

<script>
    var ctx = document.getElementById('priceHistoryChart').getContext('2d'),
        priceHistoryChart = null,
        chartDataCurrency = '',
        chartData = {ldelim}
        labels:   [],
        datasets: [
            {
                fillColor:            "rgba(220,220,220,0.2)",
                strokeColor:          "rgba(220,220,220,1)",
                pointColor:           "rgba(220,220,220,1)",
                pointStrokeColor:     "#fff",
                pointHighlightFill:   "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data:                 []
            }
        ]
    {rdelim};

    {foreach from=$preisverlaufData|array_reverse item=pv}
    chartData.labels.push('{$pv->date}');
    chartData.datasets[0].data.push('{$pv->fPreis}');
    chartDataCurrency = '{$pv->currency}';
    {/foreach}
    {if $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen === 'N'}
    $(function() {ldelim}
        window.priceHistoryChart = new Chart(window.ctx).Bar(window.chartData, {ldelim}
            responsive:      true,
            scaleBeginAtZero: false,
            tooltipTemplate: "<%if (label){ldelim}%><%=label%> - <%{rdelim}%><%= parseFloat(value).toFixed(2).replace('.', ',') %> " + window.chartDataCurrency
        {rdelim});
    {rdelim});
    {/if}
</script>
