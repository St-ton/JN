{literal}
<script type="text/javascript">
    $(document).ready(function () {
        $('.edit').on('click', function () {
            var kWarenlager = $(this).attr('id').replace('btn_', ''),
                row = $('.row_' + kWarenlager);
            if (row.css('display') === 'none') {
                row.fadeIn();
            } else {
                row.fadeOut();
            }
        });
    });
</script>
{/literal}

<div id="content" class="container-fluid">
    {if $oWarenlager_arr|@count > 0}
        <form method="post" action="warenlager.php">
            {$jtl_token}
            <input name="a" type="hidden" value="update" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('warenlager')}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="checkext">{__('watenlagerActive')}</th>
                            <th>{__('warenlagerIntern')}</th>
                            <th>{__('description')}</th>
                            <th>{__('options')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oWarenlager_arr as $oWarenlager}
                            <tr>
                                <td class="checkext">
                                    <input name="kWarenlager[]" type="checkbox" value="{$oWarenlager->kWarenlager}"{if $oWarenlager->nAktiv == 1} checked{/if} />
                                </td>
                                <td class="tcenter large">{$oWarenlager->cName}</td>
                                <td class="tcenter">{$oWarenlager->cBeschreibung}</td>
                                <td class="tcenter">
                                    <a class="btn btn-default" data-toggle="collapse" href="#collapse-{$oWarenlager->kWarenlager}" title="{__('edit')}"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                            <tr class="collapse" id="collapse-{$oWarenlager->kWarenlager}">
                                <td colspan="4">
                                {foreach $oSprache_arr as $oSprache}
                                    {assign var=kSprache value=$oSprache->kSprache}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <label for="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]">{$oSprache->cNameDeutsch}</label>
                                            </span>
                                            <input id="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]" name="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]" type="text" value="{if isset($oWarenlager->cSpracheAssoc_arr[$kSprache])}{$oWarenlager->cSpracheAssoc_arr[$kSprache]}{/if}" class="form-control large" />
                                        </div>
                                {/foreach}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <button name="update" type="submit" title="{__('refresh')}" class="btn btn-primary"><i class="fa fa-refresh"></i> {__('refresh')}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>