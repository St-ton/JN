<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"> {__('Order details')}</h2>
            <button type="button" class="close" data-dismiss="modal">
                <i class="fal fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <hr>
            <div class="last_order">
                <div class="row">
                    <div class="col-4">{__('Order number')}:</div>
                    <div class="col-8"><strong>{$oBestellung->cBestellNr}</strong></div>
                </div>
                <div class="row">
                    <div class="col-4">{__('Order date')}:</div>
                    <div class="col-8"><strong>{$oBestellung->dErstelldatum_de}</strong></div>
                </div>
            </div>
            {if count($oBestellung->Positionen) > 0}
                <div class="mt-5">
                    <div class="table-responsive">
                        <table class="table table-border">
                            <thead>
                                <tr>
                                    <th>{__('Count')}</th>
                                    <th>{__('Item')}</th>
                                    <th>{__('price')}</th>
                                </tr>
                            </thead>
                            {foreach $oBestellung->Positionen as $position}
                                {include file=$cDetailPosition Position=$position Bestellung=$oBestellung}
                            {/foreach}
                        </table>
                    </div>
                </div>
            {/if}
        </div>
        <div class="modal-footer">
            <div class="row">
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">{__('Close')}</button>
                </div>
            </div>
        </div>
    </div>
</div>
