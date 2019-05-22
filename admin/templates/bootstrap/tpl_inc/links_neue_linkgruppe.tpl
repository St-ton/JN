{assign var=cTitel value=__('newLinkGroup')}
{if $linkGroup !== null}
    {assign var=cTitel value=__('saveLinkGroup')}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel}

<div id="content">
    <form name="linkgruppe_erstellen" method="post" action="links.php">
        {$jtl_token}
        <input type="hidden" name="kLinkgruppe" value="{if $linkGroup !== null}{$linkGroup->getID()}{/if}" />

        <div class="settings">
            <div class="input-group{if isset($xPlausiVar_arr.cName)} error{/if}">
                <span class="input-group-addon">
                    <label for="cName">{__('linkGroup')}{if isset($xPlausiVar_arr.cName)} <span class="fillout">{__('FillOut')}</span>{/if}</label>
                </span>
                <input type="text" name="cName" id="cName"  class="form-control{if isset($xPlausiVar_arr.cName)} fieldfillout{/if}" value="{if isset($xPostVar_arr.cName)}{$xPostVar_arr.cName}{elseif $linkGroup !== null}{$linkGroup->getGroupName()}{/if}" />
            </div>

            <div class="input-group{if isset($xPlausiVar_arr.cTemplatename)} error{/if}">
                <span class="input-group-addon">
                    <label for="cTemplatename">{__('linkGroupTemplatename')}{if isset($xPlausiVar_arr.cTemplatename)} <span class="fillout">{__('FillOut')}</span>{/if}</label>
                </span>
                <input type="text" name="cTemplatename" id="cTemplatename" class="form-control{if isset($xPlausiVar_arr.cTemplatename)} fieldfillout{/if}" value="{if isset($xPostVar_arr.cTemplatename)}{$xPostVar_arr.cTemplatename}{elseif $linkGroup !== null}{$linkGroup->getTemplate()}{/if}" />
            </div>
            {foreach $sprachen as $sprache}
                {assign var=cISO value=$sprache->cISO}
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName_{$cISO}">{__('showedName')} ({$sprache->cNameDeutsch})</label>
                    </span>
                    <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if $linkGroup !== null}{$linkGroup->getName($sprache->kSprache)}{/if}" />
                </div>
            {/foreach}
        </div>
        <div class="save_wrapper">
            <button type="submit" class="btn btn-primary" name="action" value="save-linkgroup"><i class="fa fa-save"></i> {$cTitel}</button>
        </div>
    </form>
</div>
