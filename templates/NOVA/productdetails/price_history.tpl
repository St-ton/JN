{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-price-history'}
    {block name='productdetails-price-history-canvas'}
        <div>
            <canvas id="priceHistoryChart" width="400" height="150"></canvas>
        </div>
    {/block}
    {block name='productdetails-price-history-script'}
        {inline_script}<script>
            var ctx = document.getElementById('priceHistoryChart').getContext('2d'),
                priceHistoryChart = null,
                chartDataCurrency = '',
                chartData = {
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
            };

            {foreach $preisverlaufData|array_reverse as $pv}
                chartData.labels.push('{$pv->date}');
                chartData.datasets[0].data.push('{$pv->fPreis}');
                chartDataCurrency = '{$pv->currency}';
            {/foreach}
        </script>{/inline_script}
    {/block}
{/block}
