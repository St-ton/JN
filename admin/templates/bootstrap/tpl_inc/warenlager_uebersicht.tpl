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

<div id="content">
    {if $oWarenlager_arr|@count > 0}
        <form method="post" action="warenlager.php">
            {$jtl_token}
            <input name="a" type="hidden" value="update" />
            <div class="card">
                <div class="card-header">
                    <span class="subheading1">{__('warenlager')}</span>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="checkext">{__('warenlagerActive')}</th>
                                <th>{__('warenlagerIntern')}</th>
                                <th>{__('description')}</th>
                                <th>{__('options')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $oWarenlager_arr as $oWarenlager}
                                <tr>
                                    <td class="checkext">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="kWarenlager[]" type="checkbox" id="store-id-{$oWarenlager->kWarenlager}" value="{$oWarenlager->kWarenlager}"{if $oWarenlager->nAktiv == 1} checked{/if} />
                                            <label class="custom-control-label" for="store-id-{$oWarenlager->kWarenlager}"></label>
                                        </div>
                                    </td>
                                    <td class="tcenter large">{$oWarenlager->cName}</td>
                                    <td class="tcenter">{$oWarenlager->cBeschreibung}</td>
                                    <td class="tcenter">
                                        <div class="btn-group">
                                            <a class="btn btn-link px-2" data-toggle="collapse" href="#collapse-{$oWarenlager->kWarenlager}" title="{__('edit')}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-edit"></span>
                                                    <span class="fas fa-edit"></span>
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse" id="collapse-{$oWarenlager->kWarenlager}">
                                    <td colspan="4">
                                    {foreach $sprachen as $language}
                                        {assign var=kSprache value=$language->getId()}
                                        <div class="form-group form-row align-items-center mb-5 mb-md-3">
                                            <label class="col col-sm-4 col-form-label text-sm-right order-1" for="cNameSprache[{$oWarenlager->kWarenlager}][{$kSprache}]">{$language->getLocalizedName()}:</label>
                                            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                                <input id="cNameSprache[{$oWarenlager->kWarenlager}][{$kSprache}]"
                                                       name="cNameSprache[{$oWarenlager->kWarenlager}][{$kSprache}]"
                                                       type="text"
                                                       value="{if isset($oWarenlager->cSpracheAssoc_arr[$kSprache])}{$oWarenlager->cSpracheAssoc_arr[$kSprache]}{/if}"
                                                       class="form-control large" />
                                            </div>
                                        </div>
                                    {/foreach}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="update" type="submit" title="{__('update')}" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
