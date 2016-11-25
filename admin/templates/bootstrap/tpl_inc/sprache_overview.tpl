{include file='tpl_inc/seite_header.tpl' cTitel=#lang# cBeschreibung=#langDesc# cDokuURL=#langURL#}
{assign var="cSearchString" value=$oFilter->getField(1)->getValue()}
<script>
    function toggleTextarea(kSektion, cWertName)
    {
        $('#cWert_' + kSektion + '_' + cWertName).show();
        $('#cWert_caption_' + kSektion + '_' + cWertName).hide();
    }
</script>
<div id="content" class="container-fluid">
    <div class="block">
        <form method="post" action="sprache.php">
            <input type="hidden" name="sprachwechsel" value="1">
            <div class="input-group p25">
                <div class="input-group-addon">
                    <label for="kSprache">Sprache:</label>
                </div>
                <select id="kSprache" name="kSprache" class="form-control" onchange="this.form.submit();">
                    {foreach $oSprache_arr as $oSprache}
                        <option value="{$oSprache->kSprache}"
                                {if (int)$smarty.session.kSprache === (int)$oSprache->kSprache}selected{/if}>
                            {$oSprache->cNameDeutsch}
                            {if $oSprache->cShopStandard === 'Y'}(Standard){/if}
                        </option>
                    {/foreach}
                </select>
            </div>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab {if $tab === 'variables'}active{/if}">
            <a data-toggle="tab" href="#variables">{#langVars#}</a>
        </li>
        <li class="tab {if $tab === 'notfound'}active{/if}">
            <a data-toggle="tab" href="#notfound">{#notFoundVars#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="variables" class="tab-pane fade {if $tab === 'variables'}active in{/if}">
            <div class="panel panel-default">
                {include file='tpl_inc/filtertools.tpl' oFilter=$oFilter}
                <table class="list table">
                    <thead>
                        <tr>
                            <th>Sektion</th>
                            <th>Variable</th>
                            <th>Inhalt</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $oWert_arr as $oWert}
                            <tr>
                                <td>{$oWert->cSektionName}</td>
                                {if $cSearchString !== ''}
                                    <td>{$oWert->cName|regex_replace:"/($cSearchString)/i":"<mark>\$1</mark>"}</td>
                                {else}
                                    <td>{$oWert->cName}</td>
                                {/if}
                                <td onclick="toggleTextarea({$oWert->kSprachsektion}, '{$oWert->cName}');"
                                    style="cursor:pointer;">
                                    <span id="cWert_caption_{$oWert->kSprachsektion}_{$oWert->cName}">
                                        {if $cSearchString !== ''}
                                            {$oWert->cWert|escape|regex_replace:"/($cSearchString)/i":"<mark>\$1</mark>"}
                                        {else}
                                            {$oWert->cWert|escape}
                                        {/if}
                                    </span>
                                    <textarea id="cWert_{$oWert->kSprachsektion}_{$oWert->cName}" class="form-control"
                                              style="display:none;">{$oWert->cWert}</textarea>
                                </td>
                                <td>Aktionen</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                <div class="panel-footer">
                    <div class="btn-group">
                        <a class="btn btn-primary" href="sprache.php?token={$smarty.session.jtl_token}&action=newvar">
                            <i class="fa fa-share"></i>
                            Variable hinzuf&uuml;gen
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="notfound" class="tab-pane fade {if $tab === 'notfound'}active in{/if}">
        </div>
    </div>
</div>