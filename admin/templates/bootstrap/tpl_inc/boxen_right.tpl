<div class="boxRight col-md-12">
    <div class="panel panel-default">
        <form action="boxen.php" method="post">
            {$jtl_token}
            <div class="panel-heading boxShow">
                <h3>Sidebar rechts</h3>
            </div>
            <div class="panel-heading boxShow">
                <input type="{if $nPage > 0}checkbox{else}hidden{/if}" name="box_show" id="box_right_show" {if isset($bBoxenAnzeigen.right) && $bBoxenAnzeigen.right}checked="checked"{/if} />
                {if $nPage > 0}
                    <label for="box_right_show">Container anzeigen</label>
                {else}
                    <a href="boxen.php?action=container&position=right&value=1&token={$smarty.session.jtl_token}" title="Auf jeder Seite aktivieren"><i class="fa fa-lg fa-eye"></i></a>
                    <a href="boxen.php?action=container&position=right&value=0&token={$smarty.session.jtl_token}" title="Auf jeder Seite deaktivieren"><i class="fa fa-lg fa-eye-slash"></i></a>
                    <span>Rechte Sidebar &uuml;berall anzeigen</span>
                {/if}
            </div>
            <ul class="list-group">
                {foreach name="box" from=$oBoxenRight_arr item=oBox}
                    {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position='right'}
                {/foreach}
                <li class="list-group-item boxSaveRow">
                    <input type="hidden" name="position" value="right" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="resort" />
                    <button type="submit" value="aktualisieren" class="btn btn-primary"><i class="fa fa-refresh"></i> aktualisieren</button>
                </li>
            </ul>
        </form>
        <div class="boxOptionRow panel-footer">
            <form name="newBoxRight" action="boxen.php" method="post" class="form-horizontal">
                {$jtl_token}
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="col-sm-3 control-label" for="newBoxRight">{#new#}:</label>
                    <div class="col-sm-9">
                        <select id="newBoxRight" name="item" class="form-control" onchange="document.newBoxRight.submit();">
                            <option value="0">{#pleaseSelect#}</option>
                            {foreach from=$oVorlagen_arr item=oVorlagen}
                                <optgroup label="{$oVorlagen->cName}">
                                    {foreach from=$oVorlagen->oVorlage_arr item=oVorlage}
                                        <option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </div>
                    <input type="hidden" name="position" value="right" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </div>
            </form>
        </div>
    </div>
</div>