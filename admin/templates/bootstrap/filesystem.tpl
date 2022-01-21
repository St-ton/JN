{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('fs') cBeschreibung=__('fsDesc') cDokuURL=__('fsUrl')}

<div id="content">
    <div id="settings">
        <form method="post" action="filesystem.php">
            {$jtl_token}
            {assign var=open value=false}
            {foreach $oConfig_arr as $cnf}
                {if $cnf->isConfigurable()}
                    <div class="form-group form-row align-items-center item{if isset($cSuche) && $cnf->getID() == $cSuche} highlight{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$cnf->getValueName()}">{$cnf->getName()}</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $cnf->getInputType() === 'number'}config-type-number{/if}">
                            {if $cnf->getInputType() === 'selectbox'}
                                <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}">
                                    {foreach $cnf->getValues() as $wert}
                                        <option value="{$wert->cWert}"{if $cnf->getSetValue() == $wert->cWert} selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            {elseif $cnf->getInputType() === 'pass'}
                                <input class="form-control" type="password" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{$cnf->getSetValue()}" tabindex="1" />
                            {elseif $cnf->getInputType() === 'number'}
                                <div class="input-group form-counter">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                            <span class="fas fa-minus"></span>
                                        </button>
                                    </div>
                                    <input class="form-control" type="number" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{$cnf->getSetValue()}" tabindex="1" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                            <span class="fas fa-plus"></span>
                                        </button>
                                    </div>
                                </div>
                            {elseif $cnf->getInputType() === 'color'}
                                {include file='snippets/colorpicker.tpl'
                                    cpID=$cnf->getValueName()
                                    cpName=$cnf->getValueName()
                                    cpValue=$cnf->getSetValue()}
                            {else}
                                <input class="form-control" type="text" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{$cnf->getSetValue()}" tabindex="1" />
                            {/if}
                        </div>
                        {if $cnf->cBeschreibung}
                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                                {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->getID()}
                            </div>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{$cnf->cName}
                            {if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|strlen > 0}
                                <span class="path"><strong>{__('settingspath')}:</strong> {$cnf->cSektionsPfad}</span>
                            {/if}
                            </div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                        {assign var=open value=true}
                {/if}
            {/foreach}
                {if $open}
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->
                {/if}
                <div class="card-footer save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="test" type="submit" value="1" class="btn btn-default btn-block">
                                <i class="fal fa-play-circle"></i> {__('methodTest')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="save" type="submit" value="1" class="btn btn-primary btn-block add">
                            {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
