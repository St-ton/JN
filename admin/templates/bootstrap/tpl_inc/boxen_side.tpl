{function sideContainerSection} {* direction, directionName, oBox_arr *}
    <div class="col-md-12">
        <div class="panel panel-default">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="panel-heading">
                    <h3>{$directionName}</h3>
                    <hr>
                </div>
                <div class="panel-heading">
                    <input type="checkbox" name="box_show" id="box_{$direction}_show" value="1"
                           {if isset($bBoxenAnzeigen.$direction) && $bBoxenAnzeigen.$direction}checked{/if}>
                    <label for="box_{$direction}_show">{#showContainer#}</label>
                </div>
                {if $oBox_arr|@count > 0}
                    <ul class="list-group">
                        <li class="list-group-item boxRow">
                            <div class="row">
                                <div class="col-sm-2">
                                    <strong>{#boxTitle#}</strong>
                                </div>
                                <div class="col-sm-1">
                                    <strong>{#boxType#}</strong>
                                </div>
                                <div class="col-sm-3">
                                    <strong>{#boxLabel#}</strong>
                                </div>
                                <div class="col-sm-2">
                                    <strong>{#boxState#}</strong>
                                </div>
                                <div class="col-sm-2">
                                    <strong>{#boxSort#}</strong>
                                </div>
                                <div class="col-sm-2">
                                    <strong>{#boxActions#}</strong>
                                </div>
                            </div>
                        </li>
                        {foreach name="box" from=$oBox_arr item=oBox}
                            {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position=$direction}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="{$direction}" />
                            <input type="hidden" name="page" value="{$nPage}" />
                            <input type="hidden" name="action" value="resort" />
                            <button type="submit" value="aktualisieren" class="btn btn-primary">
                                <i class="fa fa-save"></i> {#save#}
                            </button>
                        </li>
                    </ul>
                {else}
                    <div class="alert alert-info" role="alert">
                        {#noBoxesAvailableFor#|replace:'%s':$directionName}
                    </div>
                {/if}
            </form>
            <div class="panel-footer">
                <form name="newBox_{$direction}" action="boxen.php" method="post" class="form-horizontal">
                    {$jtl_token}
                    <div class="form-group row" style="margin-bottom: 0;">
                        <div class="col-sm-2">
                            <label class="control-label" for="newBox_{$direction}">{#new#}:</label>
                        </div>
                        <div class="col-sm-10">
                            <select id="newBox_{$direction}" name="item" class="form-control" onchange="document.newBox_{$direction}.submit();">
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
                    <input type="hidden" name="position" value="{$direction}" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div>
        </div>
    </div>
{/function}

{if isset($oBoxenContainer.left) && $oBoxenContainer.left === true}
    {sideContainerSection direction='left' directionName=#sectionLeft# oBox_arr=$oBoxenLeft_arr}
{/if}
{if isset($oBoxenContainer.right) && $oBoxenContainer.right === true}
    {sideContainerSection direction='right' directionName=#sectionRight# oBox_arr=$oBoxenRight_arr}
{/if}