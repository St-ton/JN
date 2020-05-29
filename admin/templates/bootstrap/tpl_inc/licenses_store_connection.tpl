<div>
    <div class="card">
        {form}
            <div class="card-header">
                <div class="heading-body">
                    {__('Overview')}
                </div>
                <div class="heading-right">
                    {if $hasAuth}
                        <button name="action" value="revoke" class="btn btn-default"><i class="fas fa-unlink"></i> {__('unlink')}</button>
                    {/if}
                </div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="row">
                    {if $hasAuth}
                        <div class="col-md-4 border-right">
                            <div class="text-center">
                                <h2>{$licenseItemUpdates->count()}</h2>
                                <p>{__('updates available')}</p>
                            </div>
                        </div>
                        <div class="col-md-4 border-right">
                            <div class="text-center">
                                <h2>{$licenses->count()}</h2>
                                <p>{__('Licensed items')}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h2>{$lastUpdate|date_format:"%d.%m.%Y %H:%M:%S"}</h2>
                                <p>{__('last update')}</p>
                                <button class="btn btn-default" id="recheck" name="action" value="recheck"><i class="fas fa-refresh"></i> {__('Refresh')}</button>
                            </div>
                        </div>
                    {else}
                        <div class="col-md-12">
                            <div class="alert alert-default" role="alert">{__('storeNotLinkedDesc')}</div>
                            <button name="action" value="redirect" class="btn btn-primary"><i class="fas fa-link"></i> {__('storeLink')}</button>
                        </div>
                    {/if}
                </div>
            </div>
        {/form}
    </div>
</div>
