{include file='tpl_inc/seite_header.tpl' cTitel=__('agbwrb') cDokuURL=__('agbwrbURL')}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="agbwrb.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="p25 input-group left">
                <span class="input-group-addon">
                    <label for="{__('changeLanguage')}">{__('changeLanguage')}:</strong></label>
                </span>
                <span class="input-group-wrap last">
                    <select id="{__('changeLanguage')}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach $Sprachen as $sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache == $smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{__('available')} {__('agbwrb')}</h3>
        </div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th class="tleft">{__('agbwrbCustomerGrp')}</th>
                <th>{__('action')}</th>
            </tr>
            </thead>
            <tbody>
            {foreach $oKundengruppe_arr as $oKundengruppe}
                <tr>
                    <td class="">{$oKundengruppe->cName}</td>
                    <td class="tcenter">
                        <a href="agbwrb.php?agbwrb=1&agbwrb_edit=1&kKundengruppe={$oKundengruppe->kKundengruppe}&token={$smarty.session.jtl_token}"
                           class="btn btn-default" title="{__('modify')}">
                            <i class="fa fa-edit"></i>
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>