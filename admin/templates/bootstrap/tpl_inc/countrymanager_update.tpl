<div id="content">
    <div class="card">
        <div class="card-body">
            <form id="country-update-form">
                {$jtl_token}
                <input type="hidden" name="action" value="{$step}" />

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cISO">{__('ISO')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" id="cISO" name="cISO" value="" tabindex="1" required/>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                    <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                        {__('cancelWithIcon')}
                    </button>
                </div>
                <div class="col-sm-6 col-lg-auto ">
                    <button type="submit" class="btn btn-primary btn-block">
                        {__('saveWithIcon')}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>