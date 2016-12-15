{function containerSection} {* direction, directionName, oBox_arr *}
    <div class="boxCenter col-md-12">
        <div class="boxContainer panel panel-default">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="panel-heading">
                    <h3>{$directionName}</h3>
                    <hr>
                </div><!-- .panel-heading -->
                <div class="panel-heading">
                    <div class="boxShow">
                        {if $nPage > 0}
                            <input type="checkbox" name="box_show"
                                   id="box_{$direction}_show"
                                   {if isset($bBoxenAnzeigen.$direction) && $bBoxenAnzeigen.$direction}checked="checked"{/if}>
                            <label for="box_{$direction}_show">Container anzeigen</label>
                        {else}
                            {if isset($bBoxenAnzeigen.$direction) && $bBoxenAnzeigen.$direction}
                                <a href="boxen.php?action=container&position={$direction}&value=0&token={$smarty.session.jtl_token}"
                                   title="Auf jeder Seite deaktivieren">
                                    <i class="fa fa-lg fa-eye-slash"></i>
                                </a>
                                <span>{$directionName} ausblenden</span>
                            {else}
                                <a href="boxen.php?action=container&position={$direction}&value=1&token={$smarty.session.jtl_token}"
                                   title="Auf jeder Seite aktivieren">
                                    <i class="fa fa-lg fa-eye"></i>
                                </a>
                                <span>{$directionName} auf jeder Seite anzeigen</span>
                            {/if}
                        {/if}
                    </div>
                </div><!-- .panel-heading -->
                <ul class="list-group">
                    {if $oBox_arr|@count > 0}
                        <li class="boxRow">
                            <div class="col-xs-3">
                                <strong>Name</strong>
                            </div>
                            <div class="col-xs-2">
                                <strong>Typ</strong>
                            </div>
                            <div class="col-xs-2">
                                <strong>Bezeichnung</strong>
                            </div>
                            <div class="col-xs-3">
                                <strong>Sortierung</strong>
                            </div>
                            <div class="col-xs-2">
                                <strong>Aktionen</strong>
                            </div>
                        </li>
                        {foreach name="box" from=$oBox_arr item=oBox}
                            {if $oBox->bContainer}
                                <li class="list-group-item boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{else}boxRowBaseContainer{/if}">
                                    <div class="col-xs-7{if $oBox->bAktiv == 0} inactive text-muted{/if}">
                                        <b>Container #{$oBox->kBox}</b>
                                    </div>
                                    <div class="boxOptions">
                                        {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                            <div class="col-xs-3">
                                                <input type="hidden" name="box[]" value="{$oBox->kBox}">
                                                <input class="form-control text-right" type="number" size="3"
                                                       name="sort[]" value="{$oBox->nSort}"
                                                       autocomplete="off" id="{$oBox->nSort}">
                                            </div>
                                            <div class="col-xs-2 modify-wrap">
                                                {if $oBox->bAktiv == 0}
                                                    <a href="boxen.php?action=activate&position={$direction}&item={$oBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                                                       title="Auf jeder Seite aktivieren">
                                                        <i class="fa fa-lg fa-eye"></i>
                                                    </a>
                                                {else}
                                                    <a href="boxen.php?action=activate&position={$direction}&item={$oBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                                                       title="Auf jeder Seite deaktivieren">
                                                        <i class="fa fa-lg fa-eye-slash"></i>
                                                    </a>
                                                {/if}
                                                {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
                                                    <a href="boxen.php?action=edit_mode&page={$nPage}&position={$direction}&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
                                                       title="{#edit#}">
                                                        <i class="fa fa-lg fa-edit"></i>
                                                    </a>
                                                {/if}
                                                <a href="boxen.php?action=del&page={$nPage}&position={$direction}&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
                                                   onclick="return confirmDelete('{$oBox->cTitel}');" title="{#remove#}">
                                                    <i class="fa fa-lg fa-trash"></i>
                                                </a>
                                            </div>
                                        {else}
                                            <b>{$oBox->nSort}</b>
                                        {/if}
                                    </div>
                                </li>
                                {foreach from=$oBox->oContainer_arr item=oContainerBox}
                                    <li class="list-group-item boxRow boxRowContainer">
                                        <div class="boxRow">
                                            <div class="col-xs-3 boxSubName
                                                        {if $oContainerBox->bAktiv == 0 || $oBox->bAktiv == 0}inactive text-muted{/if}">
                                                {$oContainerBox->cTitel}
                                            </div>
                                            <div class="col-xs-2
                                                        {if $oContainerBox->bAktiv == 0 || $oBox->bAktiv == 0}inactive text-muted{/if}">
                                                {$oContainerBox->eTyp|ucfirst}
                                            </div>
                                            <div class="col-xs-2
                                                        {if $oContainerBox->bAktiv == 0 || $oBox->bAktiv == 0}inactive text-muted{/if}">
                                                {$oContainerBox->cName}
                                            </div>
                                            <div class="boxOptions">
                                                {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                                    <div class="col-xs-3">
                                                        <input type="hidden" name="box[]" value="{$oContainerBox->kBox}">
                                                        <input class="form-control text-right" type="number" size="3"
                                                               name="sort[]" value="{$oContainerBox->nSort}"
                                                               autocomplete="off" id="{$oContainerBox->nSort}">
                                                    </div>
                                                    <div class="col-xs-2 modify-wrap">
                                                        {if $oContainerBox->bAktiv == 0}
                                                            <a href="boxen.php?action=activate&position={$direction}&item={$oContainerBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                                                               title="Auf jeder Seite aktivieren">
                                                                <i class="fa fa-lg fa-eye"></i>
                                                            </a>
                                                        {else}
                                                            <a href="boxen.php?action=activate&position={$direction}&item={$oContainerBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                                                               title="Auf jeder Seite deaktivieren">
                                                                <i class="fa fa-lg fa-eye-slash"></i>
                                                            </a>
                                                        {/if}
                                                        {if isset($oContainerBox->eTyp) &&
                                                            ($oContainerBox->eTyp === 'text' ||
                                                                $oContainerBox->eTyp === 'link' || $oContainerBox->eTyp === 'catbox')
                                                        }
                                                            <a href="boxen.php?action=edit_mode&page={$nPage}&position={$direction}&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}"
                                                               title="{#edit#}">
                                                                <i class="fa fa-lg fa-edit"></i>
                                                            </a>
                                                        {/if}
                                                        <a href="boxen.php?action=del&page={$nPage}&position={$direction}&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}"
                                                           onclick="return confirmDelete('{$oContainerBox->cTitel}');"
                                                           title="{#remove#}">
                                                            <i class="fa fa-lg fa-trash"></i>
                                                        </a>
                                                    </div>
                                                {else}
                                                    <b>{$oContainerBox->nSort}</b>
                                                {/if}
                                            </div>
                                        </div>
                                    </li>
                                {/foreach}
                            {else}
                                {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position=$direction}
                            {/if}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="{$direction}" />
                            <input type="hidden" name="page" value="{$nPage}" />
                            <input type="hidden" name="action" value="resort" />
                            <button type="submit" value="aktualisieren" class="btn btn-primary">
                                <i class="fa fa-refresh"></i> aktualisieren
                            </button>
                        </li>
                    {/if}
                </ul>
            </form>
            <div class="panel-footer boxOptionRow">
                <form name="newBoxTop" action="boxen.php" method="post" class="form-horizontal">
                    {$jtl_token}
                    <div class="form-group row">
                        <div class="col-sm-2">
                            <label class="control-label" for="newBox_{$direction}">{#new#}:</label>
                        </div>
                        <div class="col-sm-10">
                            <select id="newBox_{$direction}" name="item" class="form-control">
                                <option value="" selected="selected">{#pleaseSelect#}</option>
                                <optgroup label="Container">
                                    <option value="0">{#newContainer#}</option>
                                </optgroup>
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

                    <div class="form-group" style="margin-bottom: 0;">
                        <div class="col-sm-2">
                            <label class="control-label" for="container_{$direction}">{#inContainer#}:</label>
                        </div>
                        <div class="col-sm-8">
                            <select id="container_{$direction}" name="container" class="form-control">
                                <option value="0">Standard</option>
                                {foreach from=$oContainerTop_arr item=oContainerTop}
                                    <option value="{$oContainerTop->kBox}">Container #{$oContainerTop->kBox}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" value="einf&uuml;gen" class="btn btn-info">
                                <i class="fa fa-level-down"></i> einf&uuml;gen
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="position" value="{$direction}" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div><!-- .panel-footer -->
        </div><!-- .boxContainer.panel -->
    </div><!-- .boxCenter -->
{/function}

{if isset($oBoxenContainer.top) && $oBoxenContainer.top === true}
    {containerSection direction='top' directionName='Header' oBox_arr=$oBoxenTop_arr}
{/if}

{if isset($oBoxenContainer.top) && $oBoxenContainer.top === true}
    {containerSection direction='bottom' directionName='Footer' oBox_arr=$oBoxenBottom_arr}
{/if}