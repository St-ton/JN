{include file='tpl_inc/seite_header.tpl' cTitel=__('agbwrb') cDokuURL=__('agbwrbURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/language_switcher.tpl'}
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="subheading1">{__('available')} {__('agbwrb')}</div>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th class="tleft">{__('customerGroup')}</th>
                    <th>{__('action')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach $oKundengruppe_arr as $oKundengruppe}
                    <tr>
                        <td class="">{$oKundengruppe->cName}</td>
                        <td class="tcenter">
                            <div class="btn-group">
                                <a href="agbwrb.php?agbwrb=1&agbwrb_edit=1&kKundengruppe={$oKundengruppe->kKundengruppe}&token={$smarty.session.jtl_token}"
                                   class="btn btn-link px-2" title="{__('modify')}">
                                    <span class="icon-hover">
                                        <span class="fal fa-edit"></span>
                                        <span class="fas fa-edit"></span>
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
</div>
