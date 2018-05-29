{if (isset($Boxen.Schnellkauf) && $Boxen.Schnellkauf->anzeigen === 'Y') || (isset($oBox->anzeigen) && $oBox->anzeigen)}
    <section class="panel panel-default box box-direct-purchase" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='quickBuy'}</div>
        </div>{* /panel-heading *}
        <div class="panel-body box-body">
            <form class="top10" action="{get_static_route id='warenkorb.php'}" method="post">
                {$jtl_token}
                <input type="hidden" name="schnellkauf" value="1">
                <div class="input-group">
                    <input aria-label="{lang key='quickBuy'}" type="text" placeholder="{lang key='productNoEAN'}"
                           class="form-control" name="ean" id="quick-purchase">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default" title="{lang key='intoBasket'}">
                            <span class="fa fa-shopping-cart"></span>
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </section>
{/if}