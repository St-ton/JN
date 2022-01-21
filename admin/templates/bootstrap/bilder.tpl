{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('imageTitle') cBeschreibung=__('bilderDesc') cDokuURL=__('bilderURL')}
<div id="content">
    <form method="post" action="bilder.php">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1">
        <div id="settings">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('imageSizes')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="table-responsive card-body">
                    <table class="list table table-border-light table-images">
                        <thead>
                        <tr>
                            <th class="text-left">{__('type')}</th>
                            <th class="text-center">{__('xs')} <small>{__('widthXHeight')}</small></th>
                            <th class="text-center">{__('sm')} <small>{__('widthXHeight')}</small></th>
                            <th class="text-center">{__('md')} <small>{__('widthXHeight')}</small></th>
                            <th class="text-center">{__('lg')} <small>{__('widthXHeight')}</small></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $indices as $idx => $name}
                        <tr>
                            <td class="text-left">{$name}</td>
                            {foreach $sizes as $size}
                            <td class="text-center">
                                <div class="input-group form-counter min-w-sm">
                                    {$optIdx = 'bilder_'|cat:$idx|cat:'_'|cat:$size|cat:'_breite'}
                                    {if !isset($oConfig.$optIdx)}
                                        {$optIdx = 'bilder_'|cat:$idx|cat:'_breite'}
                                    {/if}
                                    <input size="4" class="form-control" type="number" name="{$optIdx}" value="{$oConfig.$optIdx}" />
                                </div>
                                <span class="cross-sign text-center">x</span>
                                <div class="input-group form-counter min-w-sm">
                                    {$optIdx = 'bilder_'|cat:$idx|cat:'_'|cat:$size|cat:'_hoehe'}
                                    {if !isset($oConfig.$optIdx)}
                                        {$optIdx = 'bilder_'|cat:$idx|cat:'_hoehe'}
                                    {/if}
                                    <input size="4" class="form-control" type="number" name="{$optIdx}" value="{$oConfig.$optIdx}" />
                                </div>
                            </td>
                            {/foreach}
                        </tr>
                        {/foreach}

                        </tbody>
                    </table>
                </div>
            </div>
            {assign var=open value=false}
            {foreach $oConfig_arr as $cnf}
            {if strpos($cnf->getValueName(), 'hoehe') === false && strpos($cnf->getValueName(), 'breite') === false}
                {if $cnf->isConfigurable()}
                    <div class="form-group form-row align-items-center{if isset($cSuche) && $cnf->getID() == $cSuche} highlight{/if}">
                        <label class="col col-sm-4 col-form-label text-sm-right order-1" for="{$cnf->getValueName()}">{$cnf->getName()}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 {if $cnf->getInputType() === 'number'}config-type-number{/if}">
                        {if $cnf->getInputType() === 'selectbox'}
                            <select class="custom-select" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}">
                                {foreach $cnf->getValues() as $wert}
                                    <option value="{$wert->cWert}" {if $cnf->getSetValue() == $wert->cWert}selected{/if}>{$wert->cName}</option>
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
                                cpID="config-{$cnf->getValueName()}"
                                cpName=$cnf->getValueName()
                                cpValue=$cnf->getSetValue()}
                        {else}
                            <input class="form-control" type="text" name="{$cnf->getValueName()}" id="{$cnf->getValueName()}" value="{$cnf->getSetValue()}" tabindex="1" />
                        {/if}
                        </div>
                        {include file='snippets/einstellungen_icons.tpl' cnf=$cnf}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="card">
                        <div class="card-header">
                            <div class="subheading1">{$cnf->getName()}
                            {if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|strlen > 0}
                                <span class="path"><strong>{__('settingspath')}:</strong> {$cnf->cSektionsPfad}</span>
                            {/if}
                            </div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                        {assign var=open value=true}
                {/if}
            {/if}
            {/foreach}
            {if $open}
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
            {/if}
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto submit">
                        <button name="speichern" type="submit" value="{__('save')}" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}
