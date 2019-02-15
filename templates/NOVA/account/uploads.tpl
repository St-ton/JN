{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($Bestellung->oUpload_arr)}
    {assign var=nNameLength value=50}
    {assign var=nImageMaxWidth value=480}
    {assign var=nImageMaxHeight value=320}
    {assign var=nImagePreviewWidth value=35}
    <div id="uploads">
        <div class="h3">{lang key='yourUploads'}</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="customerupload">
                <thead>
                <tr>
                    <th class="text-center">{lang key='name'}</th>
                    <th class="text-center">{lang key='uploadFilesize'}</th>
                    <th class="text-center">{lang key='uploadAdded'}</th>
                    <th class="text-center">{lang key='uploadFile'}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $Bestellung->oUpload_arr as $oUpload}
                    <tr>
                        <td class="text-center vcenter">{$oUpload->cName}</td>
                        <td class="text-center vcenter">{$oUpload->cGroesse}</td>
                        <td class="text-center vcenter">
                            <span class="infocur" title="{$oUpload->dErstellt|date_format:'%d.%m.%Y - %H:%M:%S'}">
                                {$oUpload->dErstellt|date_format:'%d.%m.%Y'}
                            </span>
                        </td>
                        <td class="text-center">
                            {form method="post" action="{get_static_route id='jtl.php'}"}
                                {input name="kUpload" type="hidden" value="{$oUpload->kUpload}"}
                                {button assign="xs" name="{$oUpload->cName}"}<i class="fa fa-download"></i>{/button}
                            {/form}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
