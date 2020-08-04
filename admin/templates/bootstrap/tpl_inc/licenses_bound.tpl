<div class="card" id="bound-licenses">
    <div class="card-header">
        {__('Bound licenses')}
        <hr class="mb-n3">
    </div>
    {if $licenses->getActive()->count() > 0}
        <table class="table table-striped">
            <thead>
            <tr>
                <th>{__('ID')}</th>
                <th>{__('Name')}</th>
                <th>{__('State')}</th>
                <th>{__('Type')}</th>
                <th>{__('Validity')}</th>
            </tr>
            </thead>
            {foreach $licenses->getActive() as $license}
                <tr>
                    <td>{$license->getID()}</td>
                    <td>{$license->getName()}</td>
                    <td>
                        {include file='tpl_inc/licenses_referenced_item.tpl' license=$license}
                    </td>
                    <td>{__($license->getLicense()->getType())}</td>
                    <td>
                        {include file='tpl_inc/licenses_license.tpl' license=$license}
                    </td>
                </tr>
            {/foreach}
        </table>
        <div class="save-wrapper">
            <div class="row">
                <div class="ml-auto col-sm-12 col-xl-auto">
                    {form}
                        <button class="btn btn-primary" id="install-all" name="action" value="install-all">
                            <i class="fa fa-share"></i> {__('Install all')}
                        </button>
                        <button class="btn btn-primary" id="update-all" name="action" value="update-all">
                            <i class="fas fa-refresh"></i> {__('Update all')}
                        </button>
                    {/form}
                </div>
            </div>
        </div>
    {else}
        <div class="card-body">
            <p class="alert alert-info"><i class="fal fa-info-circle"></i> {__('noData')}</p>
        </div>
    {/if}
</div>
