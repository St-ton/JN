<div class="boxLeft col-md-12">
    <div class="panel panel-default">
        <form action="boxen.php" method="post">
            {$jtl_token}
            <div class="panel-heading">
                <h3>Sidebar links</h3>
                <hr>
            </div>
            <div class="panel-heading">
                <div class="boxShow">
                    {if $nPage > 0}
                        <input type="checkbox" name="box_show" id="box_left_show"{if isset($bBoxenAnzeigen.left) && $bBoxenAnzeigen.left} checked="checked"{/if} />
                        <label for="box_left_show">Container anzeigen</label>
                    {else}
                        {if isset($bBoxenAnzeigen.left) && $bBoxenAnzeigen.left}
                            <a href="boxen.php?action=container&position=left&value=0&token={$smarty.session.jtl_token}"
                               title="Sidebar links auf jeder Seite deaktivieren" class="btn btn-danger"
                               data-toggle="tooltip" data-placement="right">
                                <i class="fa fa-eye-slash"></i>
                            </a>
                        {else}
                            <a href="boxen.php?action=container&position=left&value=1&token={$smarty.session.jtl_token}"
                               title="Sidebar links auf jeder Seite aktivieren" class="btn btn-success"
                               data-toggle="tooltip" data-placement="right">
                                <i class="fa fa-eye"></i>
                            </a>
                        {/if}
                    {/if}
                </div>
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
                {foreach name="box" from=$oBoxenLeft_arr item=oBox}
                    {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position='left'}
                {/foreach}
                <li class="list-group-item boxSaveRow">
                    <input type="hidden" name="position" value="left" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="resort" />
                    <button type="submit" value="aktualisieren" class="btn btn-primary"><i class="fa fa-refresh"></i> aktualisieren</button>
                </li>
            </ul>
        </form>
        <div class="boxOptionRow panel-footer">
            <form name="newBoxLeft" action="boxen.php" method="post" class="form-horizontal">
                {$jtl_token}
                <div class="form-group row" style="margin-bottom: 0;">
                    <div class="col-sm-2">
                        <label class="control-label" for="newBoxLeft">{#new#}:</label>
                    </div>
                    <div class="col-sm-10">
                        <select id="newBoxLeft" name="item" class="form-control" onchange="document.newBoxLeft.submit();">
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
                </div>
                <input type="hidden" name="position" value="left" />
                <input type="hidden" name="page" value="{$nPage}" />
                <input type="hidden" name="action" value="new" />
            </form>
        </div>
    </div>
</div>