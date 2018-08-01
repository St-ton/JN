{*
    Params:
    linechart   - linechart object
    headline    - string
    id          - string
    width       - string
    height      - string
    ylabel      - string
    href        - bool
    legend      - bool
    ymin        - string
*}

{config_load file="$lang.conf" section="statistics"}
 
{if $linechart->getActive()}
    <div id="{$id}" style="width: {$width}; height: {$height};"></div>
    
    <script type="text/javascript">
        var chart;

        $(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: '{$id}',
                    defaultSeriesType: 'line',
                    marginRight: 0,
                    marginBottom: 50,
                    spacingBottom: 25,
                    backgroundColor: null,
                    borderColor: '#CCC',
                    borderWidth: 1
                },
                title: {
                    style: {
                        color: '#333'
                    },
                    text: '{$headline}',
                    x: -20 //center
                },
                {if $href}
                    plotOptions: {
                        series: {
                            cursor: 'pointer',
                            point: {
                                events: {
                                    click: function() {
                                        location.href = this.options.url;
                                    }
                                }
                            }
                        }
                    },
                {/if}
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 100,
                    borderWidth: 0,
                    enabled: {if $legend}true{else}false{/if},
                },
                xAxis: {$linechart->getAxisJSON()},
                yAxis: {
                    title: {
                        style: {
                            color: '#333'
                        },
                        text: '{$ylabel}'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }],
                    {if isset($ymin) && $ymin|@count_characters > 0}
                        min: {$ymin}
                    {/if}
                },
                series: {$linechart->getSeriesJSON()}
            });
        });
    </script>
{else}
    <div class="alert alert-info" role="alert">{#statisticNoData#}</div>
{/if}