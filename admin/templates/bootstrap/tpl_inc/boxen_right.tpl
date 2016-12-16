<div class="boxRight col-md-12">
    <div class="panel panel-default">
        <form action="boxen.php" method="post">
            {$jtl_token}
            <div class="panel-heading boxShow">
                <h3>Sidebar rechts</h3>
                <hr>
            </div>
            <div class="panel-heading boxShow">
                {if $nPage > 0}
                    <input type="checkbox" name="box_show" id="box_right_show"{if isset($bBoxenAnzeigen.right) && $bBoxenAnzeigen.right} checked="checked"{/if} />
                    <label for="box_right_show">Container anzeigen</label>
                {else}
                    {if isset($bBoxenAnzeigen.right) && $bBoxenAnzeigen.right}
                        <a href="boxen.php?action=container&position=right&value=0&token={$smarty.session.jtl_token}"
                           title="Sidebar rechts auf jeder Seite deaktivieren" class="btn btn-danger"
                           data-toggle="tooltip" data-placement="right">
                            <i class="fa fa-eye-slash"></i>
                        </a>
                    {else}
                        <a href="boxen.php?action=container&position=right&value=1&token={$smarty.session.jtl_token}"
                           title="Sidebar rechts auf jeder Seite aktivieren" class="btn btn-success"
                           data-toggle="tooltip" data-placement="right">
                            <i class="fa fa-eye"></i>
                        </a>
                    {/if}
                {/if}
            </div>
            <ul class="list-group">
                <li class="boxRow">
                    <div class="col-xs-3">
                        <strong>Name</strong>
                    </div>
                    <div class="col-xs-2">
                        <strong>Typ</strong>
                    </div>
                    <div class="col-xs-3">
                        <strong>Bezeichnung</strong>
                    </div>
                    <div class="col-xs-2">
                        <strong>Sortierung</strong>
                    </div>
                    <div class="col-xs-2">
                        <strong>Aktionen</strong>
                    </div>
                </li>
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
                <div class="form-group row" style="margin-bottom: 0;">
                    <div class="col-sm-2">
                        <label class="control-label" for="newBoxRight">{#new#}:</label>
                    </div>
                    <div class="col-sm-10">
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