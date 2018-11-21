{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='ftp'}

{if isset($isShopRoot)}
    {if $isShopRoot}
        {$cHinweis = #ftpValidConnection#}
    {else}
        {$cFehler = #ftpInvalidShopRoot#}
    {/if}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=#ftp# cBeschreibung=#ftpDesc# cDokuURL=#ftpUrl#}

<div id="content" class="container-fluid">
    <div id="settings">
        <form method="post" action="ftp.php">
            {$jtl_token}
            {assign var=open value=false}
            {foreach name=conf from=$oConfig_arr item=cnf}
                {if $cnf->cConf === 'Y'}
                    <div class="input-group item{if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                        <span class="input-group-addon">
                            <label for="{$cnf->cWertName}">{$cnf->cName}</label>
                        </span>
                        {if $cnf->cInputTyp === 'selectbox'}
                            <span class="input-group-wrap">
                                <select class="form-control" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                    {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                        <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            </span>
                        {elseif $cnf->cInputTyp === 'pass'}
                            <input class="form-control" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {elseif $cnf->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {elseif $cnf->cInputTyp === 'color'}
                            <span class="input-group-colorpicker-wrap">
                            <div id="{$cnf->cWertName}" style="display:inline-block">
                                <div style="background-color: {$cnf->gesetzterWert}" class="colorSelector"></div>
                            </div>
                            <input type="hidden" name="{$cnf->cWertName}" class="{$cnf->cWertName}_data" value="{$cnf->gesetzterWert}" />
                            <script type="text/javascript">
                                $('#{$cnf->cWertName}').ColorPicker({ldelim}
                                    color:    '{$cnf->gesetzterWert}',
                                    onShow:   function (colpkr) {ldelim}
                                        $(colpkr).fadeIn(500);
                                        return false;
                                    {rdelim},
                                    onHide:   function (colpkr) {ldelim}
                                        $(colpkr).fadeOut(500);
                                        return false;
                                    {rdelim},
                                    onChange: function (hsb, hex, rgb) {ldelim}
                                        $('#{$cnf->cWertName} div').css('backgroundColor', '#' + hex);
                                        $('.{$cnf->cWertName}_data').val('#' + hex);
                                    {rdelim}
                                {rdelim});
                            </script>
                            </span>
                        {else}
                            <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {/if}

                        <span class="input-group-addon">
                            {if $cnf->cBeschreibung}
                                {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                            {/if}
                        </span>
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{$cnf->cName}
                            <span class="pull-right">{getHelpDesc cID=$cnf->kEinstellungenConf}</span>
                            {if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|strlen > 0}
                                <span class="path"><strong>{#settingspath#}:</strong> {$cnf->cSektionsPfad}</span>
                            {/if}
                            </h3>
                        </div>
                        <div class="panel-body">
                        {assign var=open value=true}
                {/if}
            {/foreach}
                {if $open}
                    </div><!-- /.panel-body -->
                </div><!-- /.panel -->
                {/if}
                <div class="btn-group">
                    <button name="save" type="submit" value="1" class="btn btn-primary add"><i class="fa fa-save"></i> {#ftpSave#}</button>
                    <button name="test" type="submit" value="1" class="btn btn-default"><i class="fa fa-play-circle-o"></i> {#ftpTest#}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}