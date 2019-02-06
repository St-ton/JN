<div class="plain-list">
{if isset($oItem->cFehler) && $oItem->cFehler|strlen > 0}
    <div class="alert alert-danger">{$oItem->cFehler}</div>
{else}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{__('buyInvoice')}</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>{__('status')}</dt>
                <dd>
                    {if $oItem->oRechnung->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {__('active')}</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {__('inactive')}</span>
                    {/if}
                </dd>

                {if $oItem->oRechnung->bAktiv}
                    <dt>{__('minOrderValue')}</dt>
                    <dd>{$oItem->oRechnung->cValMin} &euro;</dd>
                    <dt>{__('maxOrderValue')}</dt>
                    <dd>{$oItem->oRechnung->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{__('buyInvoiceB2B')}</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>{__('status')}</dt>
                <dd>
                    {if $oItem->oRechnungB2B->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {__('active')}</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {__('inactive')}</span>
                    {/if}
                </dd>

                {if $oItem->oRechnungB2B->bAktiv}
                    <dt>{__('minOrderValue')}</dt>
                    <dd>{$oItem->oRechnungB2B->cValMin} &euro;</dd>
                    <dt>{__('maxOrderValue')}</dt>
                    <dd>{$oItem->oRechnungB2B->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{__('directDebit')}</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>{__('status')}</dt>
                <dd>
                    {if $oItem->oLastschrift->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {__('active')}</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {__('inactive')}</span>
                    {/if}
                </dd>

                {if $oItem->oLastschrift->bAktiv}
                    <dt>{__('minOrderValue')}</dt>
                    <dd>{$oItem->oLastschrift->cValMin} &euro;</dd>
                    <dt>{__('maxOrderValue')}</dt>
                    <dd>{$oItem->oLastschrift->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{__('payTimely')}</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>{__('status')}</dt>
                <dd>
                    {if $oItem->oRatenzahlung->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {__('active')}</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {__('inactive')}</span>
                    {/if}
                </dd>

                {if $oItem->oRatenzahlung->bAktiv}
                    <dt>{__('minOrderValue')}</dt>
                    <dd>{$oItem->oRatenzahlung->cValMin} &euro;</dd>
                    <dt>{__('maxOrderValue')}</dt>
                    <dd>{$oItem->oRatenzahlung->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>


    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{__('payLater')}</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>{__('status')}</dt>
                <dd>
                    {if $oItem->oPaylater->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {__('active')}</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {__('inactive')}</span>
                    {/if}
                </dd>

                {if $oItem->oPaylater->bAktiv}
                    <dt>{__('minOrderValue')}</dt>
                    <dd>{$oItem->oPaylater->cValMin} &euro;</dd>
                    <dt>{__('maxOrderValue')}</dt>
                    <dd>{$oItem->oPaylater->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{__('payLaterB2B')}</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>{__('status')}</dt>
                <dd>
                    {if $oItem->oPaylaterB2B->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> {__('active')}</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {__('inactive')}</span>
                    {/if}
                </dd>

                {if $oItem->oPaylaterB2B->bAktiv}
                    <dt>{__('minOrderValue')}</dt>
                    <dd>{$oItem->oPaylaterB2B->cValMin} &euro;</dd>
                    <dt>{__('maxOrderValue')}</dt>
                    <dd>{$oItem->oPaylaterB2B->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>


{/if}
</div>