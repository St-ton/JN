{if !isset($kPlugin)}
    {assign var=kPlugin value=0}
{/if}
{foreach $list as $link}
    {assign var=missingLinkTranslations value=$link->getMissingTranslations()}
    <tr class="link-item{if $kPlugin > 0 && $kPlugin == $link->getPluginID()} highlight{/if}{if $link->getLevel() == 0} main{/if}">
        {math equation="a * b" a=$link->getLevel()-1 b=20 assign=fac}
        <td style="width: 40%">
            <div style="margin-left:{if $fac > 0}{$fac}px{else}0{/if}; padding-top: 7px" {if $link->getLevel() > 0 && $link->getParent() > 0}class="sub"{/if}>
                {$link->getName()}{if $missingLinkTranslations|count > 0} <i title="Fehlende Übersetzungen: {$missingLinkTranslations|count}" class="fa fa-warning"></i>{/if}
            </div>
        </td>
        <td class="tcenter floatforms" style="width: 50%">
            <form class="navbar-form2 p33 left" method="post" action="links.php" name="aenderlinkgruppe_{$link->getID()}_{$id}">
                {$jtl_token}
                <input type="hidden" name="aender_linkgruppe" value="1" />
                <input type="hidden" name="kLink" value="{$link->getID()}" />
                <input type="hidden" name="kLinkgruppeAlt" value="{$id}" />
                {if $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                <select title="{#linkGroupMove#}" class="form-control" name="kLinkgruppe" onchange="document.forms['aenderlinkgruppe_{$link->getID()}_{$id}'].submit();">
                    <option value="-1">{#linkGroupMove#}</option>
                    {foreach name=aenderlinkgruppe from=$linkgruppen item=linkgruppeTMP}
                        {if $linkgruppeTMP->getID() != $id && $linkgruppeTMP->getID() > 0}
                            <option value="{$linkgruppeTMP->getID()}">{$linkgruppeTMP->getName()}</option>
                        {/if}
                    {/foreach}
                </select>
            </form>
            <form class="navbar-form2 p33 left" method="post" action="links.php" name="kopiereinlinkgruppe_{$link->getID()}_{$id}">
                {$jtl_token}
                <input type="hidden" name="kopiere_in_linkgruppe" value="1" />
                <input type="hidden" name="kLink" value="{$link->getID()}" />
                {if $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                {if $id > 0}
                    <select title="{#linkGroupCopy#}" class="form-control" name="kLinkgruppe" onchange="document.forms['kopiereinlinkgruppe_{$link->getID()}_{$id}'].submit();">
                        <option value="-1">{#linkGroupCopy#}</option>
                        {foreach name=kopiereinlinkgruppe from=$linkgruppen item=linkgruppeTMP}
                            {if $linkgruppeTMP->getID() != $id && $linkgruppeTMP->getID() > 0}
                                <option value="{$linkgruppeTMP->getID()}">{$linkgruppeTMP->getName()}</option>
                            {/if}
                        {/foreach}
                    </select>
                {/if}
            </form>
            <form class="navbar-form2 p33 left" method="post" action="links.php" name="aenderlinkvater_{$link->getID()}_{$id}">
                {$jtl_token}
                <input type="hidden" name="aender_linkvater" value="1" />
                <input type="hidden" name="kLink" value="{$link->getID()}" />
                <input type="hidden" name="kLinkgruppe" value="{$id}" />
                {if $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                {if $id > 0}
                    <select title="{#linkMove#}" class="form-control" name="kVaterLink" onchange="document.forms['aenderlinkvater_{$link->getID()}_{$id}'].submit();">
                        <option value="-1">{#linkMove#}</option>
                        <option value="0">-- Root --</option>
                        {foreach $list as $linkTMP}
                            {if $linkTMP->getID() !== $link->getID() && $linkTMP->getID() !== $link->getParent()}
                                <option value="{$linkTMP->getID()}">{$linkTMP->getName()}</option>
                            {/if}
                        {/foreach}
                    </select>
                {/if}
            </form>
        </td>
        <td class="tcenter" style="width: 10%;min-width: 143px;">
            <form method="post" action="links.php">
                {$jtl_token}
                {if $kPlugin > 0}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                {/if}
                <input type="hidden" name="kLinkgruppe" value="{$id}" />
                <div class="btn-group">
                    {if $id > 0}
                        <button name="kLink" value="{$link->getID()}" class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></button>
                        <button name="removefromlinkgroup" value="{$link->getID()}" class="btn btn-warning" title="{#linkGroupRemove#}"><i class="fa fa-unlink"></i></button>
                    {/if}
                    <button name="dellink" value="{$link->getID()}" class="btn btn-danger{if $link->getPluginID() > 0} disabled{/if}"{if $link->getPluginID() === 0} onclick="return confirmDelete();"{/if} title="{#delete#}"><i class="fa fa-trash"></i></button>
                </div>
            </form>
        </td>
    </tr>
    {if $link->getChildLinks()->count() > 0}
        {include file="tpl_inc/links_uebersicht_item.tpl" list=$link->getChildLinks() id=$id}
    {/if}
{/foreach}