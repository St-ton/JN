{if isset($oBoxenContainer.top) && $oBoxenContainer.top === true}
    <div class="boxCenter col-md-12">
        <div class="boxContainer panel panel-default">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="panel-heading">
                    <h3 class="panel-title">Top (Container &uuml;ber dem Seiteninhalt)</h3>
                    <hr>
                </div>
                <div class="panel-heading">
                    <span class="boxShow">
                        <label style="margin:0;" for="box_top_show"><input type="checkbox" name="box_show" id="box_top_show"{if isset($bBoxenAnzeigen.top) && $bBoxenAnzeigen.top} checked="checked"{/if} /> Top-Container anzeigen</label>
                    </span>
                </div>
                <ul class="list-group">
                    {if $oBoxenTop_arr|@count > 0}
                        {foreach name="box" from=$oBoxenTop_arr item=oBox}
                            {if $oBox->bContainer}
                                <li class="list-group-item boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{else}boxRowBaseContainer{/if}">
                                    <div class="left">
                                        <b>Container #{$oBox->kBox}</b>
                                    </div>
                                    <div class="boxOptions">
                                        {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                            <input type="hidden" name="box[]" value="{$oBox->kBox}" />
                                            <input class="form-control" type="text" size="3" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
                                            <div class="modify-wrap">
                                                <input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
                                                {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
                                                    <a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oBox->kBox}&token={$smarty.session.jtl_token}" title="{#edit#}"><i class="fa fa-lg fa-edit"></i></a>
                                                {/if}
                                                <a href="boxen.php?action=del&page={$nPage}&position=top&item={$oBox->kBox}&token={$smarty.session.jtl_token}" onclick="return confirmDelete('{$oBox->cTitel}');" title="{#remove#}"><i class="fa fa-lg fa-trash"></i></a>
                                            </div>
                                        {else}
                                            <b>{$oBox->nSort}</b>
                                        {/if}
                                    </div>
                                    <div class="boxBlockContainer clear container-child">
                                        <!-- container -->
                                        {foreach from=$oBox->oContainer_arr item=oContainerBox}
                                            <div class="boxRowContainer">
                                                <div class="boxRow">
                                                    <div class="left">
                                                        {$oContainerBox->cTitel}
                                                    </div>
                                                    <div class="boxOptions">
                                                        {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                                            <input type="hidden" name="box[]" value="{$oContainerBox->kBox}" />
                                                            <input class="form-control" type="text" size="3" name="sort[]" value="{$oContainerBox->nSort}" autocomplete="off" id="{$oContainerBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
                                                            <div class="modify-wrap">
                                                                <input type="checkbox" name="aktiv[]" {if $oContainerBox->bAktiv == 1}checked="checked"{/if} value="{$oContainerBox->kBox}" />
                                                                {if isset($oContainerBox->eTyp) && ($oContainerBox->eTyp === 'text' || $oContainerBox->eTyp === 'link' || $oContainerBox->eTyp === 'catbox')}
                                                                    <a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}" title="{#edit#}"><i class="fa fa-lg fa-edit"></i></a>
                                                                {/if}
                                                                <a href="boxen.php?action=del&page={$nPage}&position=top&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}" onclick="return confirmDelete('{$oContainerBox->cTitel}');" title="{#remove#}"><i class="fa fa-lg fa-trash"></i></a>
                                                            </div>
                                                        {else}
                                                            <b>{$oContainerBox->nSort}</b>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                        {/foreach}
                                        <!-- //container -->
                                    </div>
                                </li>
                            {else}
                                <li class="list-group-item boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{/if}">
                                    <div class="left">
                                        {$oBox->cTitel}
                                    </div>
                                    <div class="boxOptions">
                                        {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                            <input type="hidden" name="box[]" value="{$oBox->kBox}" />
                                            <input class="form-control" type="text" size="3" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
                                            <div class="modify-wrap">
                                                <input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
                                                {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
                                                    <a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oBox->kBox}&token={$smarty.session.jtl_token}" title="{#edit#}"><i class="fa fa-lg fa-edit"></i></a>
                                                {/if}
                                                <a href="boxen.php?action=del&page={$nPage}&position=top&item={$oBox->kBox}&token={$smarty.session.jtl_token}" onclick="return confirmDelete('{$oBox->cTitel}');" title="{#remove#}"><i class="fa fa-lg fa-trash"></i></a>
                                            </div>
                                        {else}
                                            <b>{$oBox->nSort}</b>
                                        {/if}
                                    </div>
                                </li>
                            {/if}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="top" />
                            <input type="hidden" name="page" value="{$nPage}" />
                            <input type="hidden" name="action" value="resort" />
                            <button type="submit" value="aktualisieren" class="btn btn-primary">aktualisieren</button>
                        </li>
                    {/if}
                </ul>
            </form>
            <div class="panel-footer boxOptionRow">
                <form name="newBoxTop" action="boxen.php" method="post" class="form-horizontal">
                    {$jtl_token}
                    <div class="form-group">
                        <label for="newBoxTop" class="col-sm-3 control-label">{#new#}:</label>
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
                        <label for="containerTop" class="col-sm-3 control-label">{#inContainer#}:</label>
                        <div class="col-sm-6">
                            <select id="containerTop" name="container" class="form-control">
                                <option value="0">Standard</option>
                                {foreach from=$oContainerTop_arr item=oContainerTop}
                                    <option value="{$oContainerTop->kBox}">Container #{$oContainerTop->kBox}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" value="einf&uuml;gen" class="btn btn-info"><i class="fa fa-level-down"></i> einf&uuml;gen</button>
                        </div>
                    </div>
                    <input type="hidden" name="position" value="top" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div>
        </div><!-- .boxContainer.panel -->
    </div>
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
                            <input type="checkbox" name="box_show" id="box_bottom_show"{if isset($bBoxenAnzeigen.bottom) && $bBoxenAnzeigen.bottom} checked="checked"{/if} />
                            <label for="box_left_show">Container anzeigen</label>
                    {else}
                        {if isset($bBoxenAnzeigen.bottom) && $bBoxenAnzeigen.bottom}
                            <a href="boxen.php?action=container&position=bottom&value=0&token={$smarty.session.jtl_token}" title="Auf jeder Seite deaktivieren"><i class="fa fa-lg fa-eye-slash"></i></a>
                            <span>Footer ausblenden</span>
                        {else}
                            <a href="boxen.php?action=container&position=bottom&value=1&token={$smarty.session.jtl_token}" title="Auf jeder Seite aktivieren"><i class="fa fa-lg fa-eye"></i></a>
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
                                        <div class="col-xs-8">
                                            <b>Container #{$oBox->kBox}</b>
                                        </div>
                                        <div class="boxOptions">
                                            {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                                <input type="hidden" name="box[]" value="{$oBox->kBox}" />
                                                <input class="form-control" type="text" size="3" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
                                                <div class="modify-wrap">
                                                    <input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
                                                    {if $oBox->eTyp === 'text' || $oBox->eTyp === 'link' || $oBox->eTyp === 'catbox'}
                                                        <a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oBox->kBox}&token={$smarty.session.jtl_token}" title="{#edit#}"><i class="fa fa-lg fa-edit"></i></a>
                                                    {/if}
                                                    <a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oBox->kBox}&token={$smarty.session.jtl_token}" onclick="return confirmDelete('{$oBox->cTitel}');" title="{#remove#}"><i class="fa fa-lg fa-trash"></i></a>
                                                </div>
                                            {else}
                                                <b>{$oBox->nSort}</b>
                                            {/if}
                                        </div>
                                    <div class="boxBlockContainer clear container-child">
                                    {foreach from=$oBox->oContainer_arr item=oContainerBox}   
                                        <div class="boxRowContainer">
                                            <div class="boxRow">
                                                <div class="col-xs-8">
                                                    {$oContainerBox->cTitel}
                                                </div>
                                                <div class="boxOptions">
                                                    {if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
                                                        <input type="hidden" name="box[]" value="{$oContainerBox->kBox}" />
                                                        <input class="form-control" type="text" size="3" name="sort[]" value="{$oContainerBox->nSort}" autocomplete="off" id="{$oContainerBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
                                                        <div class="modify-wrap">
                                                            <input type="checkbox" name="aktiv[]" {if $oContainerBox->bAktiv == 1}checked="checked"{/if} value="{$oContainerBox->kBox}" />
                                                            {if isset($oContainerBox->eTyp) && ($oContainerBox->eTyp === 'text' || $oContainerBox->eTyp === 'link' || $oContainerBox->eTyp === 'catbox')}
                                                                <a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}" title="{#edit#}"><i class="fa fa-lg fa-edit"></i></a>
                                                            {/if}
                                                            <a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oContainerBox->kBox}&token={$smarty.session.jtl_token}" onclick="return confirmDelete('{$oContainerBox->cTitel}');" title="{#remove#}"><i class="fa fa-lg fa-trash"></i></a>
                                                        </div>
                                                    {else}
                                                        <b>{$oContainerBox->nSort}</b>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                    </div>
                                </li>
                            {else}
                                {include file="tpl_inc/box_single.tpl" oBox=$oBox nPage=$nPage position='bottom'}
                            {/if}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="bottom" />
                            <input type="hidden" name="page" value="{$nPage}" />
                            <input type="hidden" name="action" value="resort" />
                            <button type="submit" value="aktualisieren" class="btn btn-primary"><i class="fa fa-refresh"></i> aktualisieren</button>
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
                            <button type="submit" value="einf&uuml;gen" class="btn btn-info"><i class="fa fa-level-down"></i> einf&uuml;gen</button>
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