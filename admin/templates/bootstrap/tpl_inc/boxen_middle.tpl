{if isset($oBoxenContainer.top) && $oBoxenContainer.top === true}
    <div class="boxCenter col-md-12">
        <div class="boxContainer panel panel-default">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="panel-heading">
                    <h3>Top</h3>
                    <hr>
                </div><!-- .panel-heading -->
                <div class="panel-heading">
                    <div class="boxShow">
                        {if $nPage > 0}
                            <input type="checkbox" name="box_show"
                                   id="box_top_show"
                                   {if isset($bBoxenAnzeigen.top) && $bBoxenAnzeigen.top}checked="checked"{/if}>
                            <label for="box_top_show">Container anzeigen</label>
                        {else}
                            {if isset($bBoxenAnzeigen.top) && $bBoxenAnzeigen.top}
                                <a href="boxen.php?action=container&position=top&value=0&token={$smarty.session.jtl_token}"
                                   title="Auf jeder Seite deaktivieren">
                                    <i class="fa fa-lg fa-eye-slash"></i>
                                </a>
                                <span>Top ausblenden</span>
                            {else}
                                <a href="boxen.php?action=container&position=top&value=1&token={$smarty.session.jtl_token}"
                                   title="Auf jeder Seite aktivieren">
                                    <i class="fa fa-lg fa-eye"></i>
                                </a>
                                <span>Top auf jeder Seite anzeigen</span>
                            {/if}
                        {/if}
                    </div>
                </div><!-- .panel-heading -->
                <ul class="list-group">
                    {if $oBoxenTop_arr|@count > 0}
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
                        {foreach name="box" from=$oBoxenTop_arr item=oBox}
                            {if $oBox->bContainer}
                                <li class="list-group-item boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{else}boxRowBaseContainer{/if}">
                                    <div class="col-xs-7">
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
                                                    <a href="boxen.php?action=activate&position=top&item={$oBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                                                       title="Auf jeder Seite aktivieren">
                                                        <i class="fa fa-lg fa-eye"></i>
                                                    </a>
                                                {else}
                                                    <a href="boxen.php?action=activate&position=top&item={$oBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                                                       title="Auf jeder Seite deaktivieren">
                                                        <i class="fa fa-lg fa-eye-slash"></i>
                                                    </a>
                                                {/if}
                                                {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
                                                    <a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
                                                       title="{#edit#}">
                                                        <i class="fa fa-lg fa-edit"></i>
                                                    </a>
                                                {/if}
                                                <a href="boxen.php?action=del&page={$nPage}&position=top&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
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
                                            <div class="col-xs-7 boxSubName">
                                                {$oContainerBox->cTitel}
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
                                                            <a href="boxen.php?action=activate&position=top&item={$oContainerBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                                                               title="Auf jeder Seite aktivieren">
                                                                <i class="fa fa-lg fa-eye"></i>
                                                            </a>
                                                        {else}
                                                            <a href="boxen.php?action=activate&position=top&item={$oContainerBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                                                               title="Auf jeder Seite deaktivieren">
                                                                <i class="fa fa-lg fa-eye-slash"></i>
                                                            </a>
                                                        {/if}
                                                        {if isset($oContainerBox->eTyp) &&
                                                            ($oContainerBox->eTyp === 'text' ||
                                                                $oContainerBox->eTyp === 'link' || $oContainerBox->eTyp === 'catbox')
                                                        }
                                                            <a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}"
                                                               title="{#edit#}">
                                                                <i class="fa fa-lg fa-edit"></i>
                                                            </a>
                                                        {/if}
                                                        <a href="boxen.php?action=del&page={$nPage}&position=top&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}"
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
                                {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position='top'}
                            {/if}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="top" />
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
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="newBoxTop">{#new#}:</label>
                        <div class="col-sm-9">
                            <select id="newBoxTop" name="item" class="form-control">
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
                        <label class="col-sm-3 control-label" for="containerTop">{#inContainer#}:</label>
                        <div class="col-sm-6">
                            <select id="containerTop" name="container" class="form-control">
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
                    <input type="hidden" name="position" value="top" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div><!-- .panel-footer -->
        </div><!-- .boxContainer.panel -->
    </div><!-- .boxCenter -->
{/if}

{if isset($oBoxenContainer.bottom) && $oBoxenContainer.bottom === true}
    <div class="boxCenter col-md-12">
        <div class="boxContainer panel panel-default">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="panel-heading">
                    <h3>Footer</h3>
                    <hr>
                </div><!-- .panel-heading -->
                <div class="panel-heading">
                    <div class="boxShow">
                        {if $nPage > 0}
                            <input type="checkbox" name="box_show"
                                   id="box_bottom_show"
                                   {if isset($bBoxenAnzeigen.bottom) && $bBoxenAnzeigen.bottom}checked="checked"{/if}>
                            <label for="box_bottom_show">Container anzeigen</label>
                        {else}
                            {if isset($bBoxenAnzeigen.bottom) && $bBoxenAnzeigen.bottom}
                                <a href="boxen.php?action=container&position=bottom&value=0&token={$smarty.session.jtl_token}"
                                   title="Auf jeder Seite deaktivieren">
                                    <i class="fa fa-lg fa-eye-slash"></i>
                                </a>
                                <span>Footer ausblenden</span>
                            {else}
                                <a href="boxen.php?action=container&position=bottom&value=1&token={$smarty.session.jtl_token}"
                                   title="Auf jeder Seite aktivieren">
                                    <i class="fa fa-lg fa-eye"></i>
                                </a>
                                <span>Footer auf jeder Seite anzeigen</span>
                            {/if}
                        {/if}
                    </div>
                </div><!-- .panel-heading -->
                <ul class="list-group">
                    {if $oBoxenBottom_arr|@count > 0}
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
                        {foreach name="box" from=$oBoxenBottom_arr item=oBox}
                            {if $oBox->bContainer}
                                <li class="list-group-item boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{else}boxRowBaseContainer{/if}">
                                    <div class="col-xs-7">
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
                                                    <a href="boxen.php?action=activate&position=bottom&item={$oBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                                                       title="Auf jeder Seite aktivieren">
                                                        <i class="fa fa-lg fa-eye"></i>
                                                    </a>
                                                {else}
                                                    <a href="boxen.php?action=activate&position=bottom&item={$oBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                                                       title="Auf jeder Seite deaktivieren">
                                                        <i class="fa fa-lg fa-eye-slash"></i>
                                                    </a>
                                                {/if}
                                                {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
                                                    <a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
                                                       title="{#edit#}">
                                                        <i class="fa fa-lg fa-edit"></i>
                                                    </a>
                                                {/if}
                                                <a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oBox->kBox}&token={$smarty.session.jtl_token}"
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
                                            <div class="col-xs-3 boxSubName">
                                                {$oContainerBox->cTitel}
                                            </div>
                                            <div class="col-xs-2{if $oContainerBox->bAktiv == 0} inactive text-muted{/if}">
                                                {$oContainerBox->eTyp|ucfirst}
                                            </div>
                                            <div class="col-xs-2{if $oContainerBox->bAktiv == 0} inactive text-muted{/if}">
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
                                                            <a href="boxen.php?action=activate&position=bottom&item={$oContainerBox->kBox}&value=1&token={$smarty.session.jtl_token}"
                                                               title="Auf jeder Seite aktivieren">
                                                                <i class="fa fa-lg fa-eye"></i>
                                                            </a>
                                                        {else}
                                                            <a href="boxen.php?action=activate&position=bottom&item={$oContainerBox->kBox}&value=0&token={$smarty.session.jtl_token}"
                                                               title="Auf jeder Seite deaktivieren">
                                                                <i class="fa fa-lg fa-eye-slash"></i>
                                                            </a>
                                                        {/if}
                                                        {if isset($oContainerBox->eTyp) &&
                                                            ($oContainerBox->eTyp === 'text' ||
                                                                $oContainerBox->eTyp === 'link' || $oContainerBox->eTyp === 'catbox')
                                                        }
                                                            <a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}"
                                                               title="{#edit#}">
                                                                <i class="fa fa-lg fa-edit"></i>
                                                            </a>
                                                        {/if}
                                                        <a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}"
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
                                {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position='bottom'}
                            {/if}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="bottom" />
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
                <form name="newBoxBottom" action="boxen.php" method="post" class="form-horizontal">
                    {$jtl_token}
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="newBoxBottom">{#new#}:</label>
                        <div class="col-sm-9">
                            <select id="newBoxBottom" name="item" class="form-control">
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
                        <label class="col-sm-3 control-label" for="containerBottom">{#inContainer#}:</label>
                        <div class="col-sm-6">
                            <select id="containerBottom" name="container" class="form-control">
                                <option value="0">Standard</option>
                                {foreach from=$oContainerBottom_arr item=oContainerBottom}
                                    <option value="{$oContainerBottom->kBox}">Container #{$oContainerBottom->kBox}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" value="einf&uuml;gen" class="btn btn-info">
                                <i class="fa fa-level-down"></i> einf&uuml;gen
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="position" value="bottom" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div><!-- .panel-footer -->
        </div><!-- .boxContainer.panel -->
    </div><!-- .boxCenter -->
{/if}