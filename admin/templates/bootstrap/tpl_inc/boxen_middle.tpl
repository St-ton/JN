{function containerSection} {* direction, directionName, oBox_arr, oContainer_arr *}
    <div class="col-md-12">
        <div class="card">
            <form action="boxen.php" method="post">
                {$jtl_token}
                <div class="card-header">
                    <div class="subheading1">{$directionName}</div>
                    <hr class="mb-3">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="box_show" id="box_{$direction}_show" value="1"
                           {if isset($bBoxenAnzeigen.$direction) && $bBoxenAnzeigen.$direction}checked{/if}>
                        <label class="custom-control-label" for="box_{$direction}_show">{__('showContainer')}</label>
                    </div>
                </div>
                <div class="card-body">
                {if $oBox_arr|@count > 0}
                    <ul class="list-group">
                        <li class="list-group-item boxRow">
                            <div class="row">
                                <div class="col-sm-2">
                                    <strong>{__('boxTitle')}</strong>
                                </div>
                                <div class="col-sm-1">
                                    <strong>{__('boxType')}</strong>
                                </div>
                                <div class="col-sm-3">
                                    <strong>{__('boxLabel')}</strong>
                                </div>
                                <div class="col-sm-2">
                                    <strong>{__('status')}</strong>
                                </div>
                                <div class="col-sm-2">
                                    <strong>{__('sorting')}</strong>
                                </div>
                                <div class="col-sm-2">
                                    <strong>{__('actions')}</strong>
                                </div>
                            </div>
                        </li>
                        {foreach $oBox_arr as $oBox}
                            {if $oBox->getBaseType() === $smarty.const.BOX_CONTAINER}
                                <div class="mt-1"></div>
                                {include file='tpl_inc/box_single.tpl' oBox=$oBox nPage=$nPage position=$direction}
                                {foreach $oBox->getChildren() as $oContainerBox}
                                    {include file='tpl_inc/box_single.tpl' oBox=$oContainerBox nPage=$nPage position=$direction}
                                {/foreach}
                                <div class="mt-1"></div>
                            {else}
                                {include file='tpl_inc/box_single.tpl' oBox=$oBox nPage=$nPage position=$direction}
                            {/if}
                        {/foreach}
                        <li class="list-group-item boxSaveRow">
                            <input type="hidden" name="position" value="{$direction}" />
                            <input type="hidden" name="page" value="{$nPage}" />
                            <input type="hidden" name="action" value="resort" />
                            <button type="submit" value="aktualisieren" class="btn btn-primary">
                                <i class="fa fa-save"></i> {__('save')}
                            </button>
                        </li>
                    </ul>
                {else}
                    <div class="alert alert-info" role="alert">
                        {__('noBoxesAvailableFor')|replace:'%s':$directionName}
                    </div>
                {/if}
                </div>
            </form>
            <div class="card-footer mb-5">
                <form name="newBox_{$direction}" action="boxen.php" method="post" class="form-horizontal">
                    {$jtl_token}
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="newBox_{$direction}">{__('new')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="newBox_{$direction}" name="item" class="custom-select">
                                <option value="" selected="selected">{__('pleaseSelect')}</option>
                                <optgroup label="Container">
                                    <option value="0">{__('newContainer')}</option>
                                </optgroup>
                                {foreach $oVorlagen_arr as $oVorlagen}
                                    <optgroup label="{$oVorlagen->cName}">
                                        {foreach $oVorlagen->oVorlage_arr as $oVorlage}
                                            <option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="container_{$direction}">{__('inContainer')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="container_{$direction}" name="container" class="custom-select">
                                <option value="0">Standard</option>
                                {foreach $oContainer_arr as $oContainer}
                                    <option value="{$oContainer->kBox}">Container #{$oContainer->kBox}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right"></label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <button type="submit" value="einfügen" class="btn btn-primary">
                                <i class="fa fa-level-down"></i> {__('insert')}
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="position" value="{$direction}" />
                    <input type="hidden" name="page" value="{$nPage}" />
                    <input type="hidden" name="action" value="new" />
                </form>
            </div><!-- .card-footer -->
        </div><!-- .boxContainer.panel -->
    </div><!-- .boxCenter -->
{/function}

{if isset($oBoxenContainer.top) && $oBoxenContainer.top === true}
    {containerSection direction='top' directionName=__('sectionTop') oBox_arr=$oBoxenTop_arr
                      oContainer_arr=$oContainerTop_arr}
{/if}

{if isset($oBoxenContainer.bottom) && $oBoxenContainer.bottom === true}
    {containerSection direction='bottom' directionName=__('sectionBottom') oBox_arr=$oBoxenBottom_arr
                      oContainer_arr=$oContainerBottom_arr}
{/if}