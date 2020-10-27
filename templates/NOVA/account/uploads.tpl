{block name='account-uploads'}
    {if !empty($Bestellung->oUpload_arr)}
        {assign var=nNameLength value=50}
        {assign var=nImageMaxWidth value=480}
        {assign var=nImageMaxHeight value=320}
        {assign var=nImagePreviewWidth value=35}
        <div id="uploads" class="mt-3">
            {block name='account-uploads-subheading'}
                <div class="h2">{lang key='yourUploads'}</div>
            {/block}
            {block name='account-uploads-content'}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="customerupload">
                        <thead>
                            {block name='account-uploads-uploads-heading'}
                            <tr>
                                <th class="text-center-util">{lang key='name'}</th>
                                <th class="text-center-util">{lang key='uploadFilesize'}</th>
                                <th class="text-center-util">{lang key='uploadAdded'}</th>
                                <th class="text-center-util">{lang key='uploadFile'}</th>
                            </tr>
                            {/block}
                        </thead>
                        <tbody>
                            {block name='account-uploads-uploads'}
                                {foreach $Bestellung->oUpload_arr as $oUpload}
                                        <tr>
                                            <td class="text-center-util vcenter">{$oUpload->cName}</td>
                                            <td class="text-center-util vcenter">{$oUpload->cGroesse}</td>
                                            <td class="text-center-util vcenter">
                                                <span class="infocur" title="{$oUpload->dErstellt|date_format:'%d.%m.%Y - %H:%M:%S'}">
                                                    {$oUpload->dErstellt|date_format:'%d.%m.%Y'}
                                                </span>
                                            </td>
                                            <td class="text-center-util">
                                                {form method="post" action="{get_static_route id='jtl.php'}" slide=true}
                                                    {input name="kUpload" type="hidden" value=$oUpload->kUpload}
                                                    {block name='account-uploads-uploads-button'}
                                                        {button type="submit" size="sm" variant="outline-primary" name=$oUpload->cName}
                                                            <i class="fa fa-download"></i>
                                                        {/button}
                                                    {/block}
                                                {/form}
                                            </td>
                                        </tr>
                                {/foreach}
                            {/block}
                        </tbody>
                    </table>
                </div>
            {/block}
        </div>
    {/if}
{/block}
