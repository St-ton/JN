<div class="widget-custom-data">
    {if count($bestsellers) > 0}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{__('Item')}</th>
                        <th class="text-center">{__('Count')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $bestsellers as $bestseller}
                        <tr>
                            <td>{$bestseller->cName}</td>
                            <td class="text-center">{$bestseller->fAnzahl|string_format:'%.0f'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <div class="alert alert-info" role="alert">
            {__('No bestsellers found.')}
        </div>
    {/if}
</div>
