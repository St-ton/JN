{include file='tpl_inc/seite_header.tpl' cTitel=__("news") cBeschreibung=__("newsDesc")}
<div id="content" class="container-fluid">
    <div class="category first clearall">
        <div class="left">{$oNews->getTitle()}</div>
        <div class="no_overflow tright">{$oNews->getDate()->format('d.m.Y H:i')}</div>
    </div>
    <div class="container-fluid">
        {$oNews->getContent()}
    </div>
    {if $oNewsKommentar_arr|@count > 0}
        <form method="post" action="news.php">
            {$jtl_token}
            <input type="hidden" name="news" value="1" />
            <input type="hidden" name="kNews" value="{$oNews->getID()}" />
            <input type="hidden" name="kommentare_loeschen" value="1" />
            {if isset($cTab)}
                <input type="hidden" name="tab" value="{$cTab}" />
            {/if}
            {if isset($cSeite)}
                <input type="hidden" name="s2" value="{$cSeite}" />
            {/if}
            <input type="hidden" name="nd" value="1" />
            <div class="category">{__("newsComments")}</div>
            {foreach name=kommentare from=$oNewsKommentar_arr item=oNewsKommentar}
                <table width="100%" cellpadding="5" cellspacing="5" class="kundenfeld">
                    <tr>
                        <td valign="top" align="left" style="width: 33%;">
                            <table  class="table">
                                <tr>
                                    <td style="width: 10px;">
                                        <input name="kNewsKommentar[]" type="checkbox" value="{$oNewsKommentar->getID()}" id="nk-{$oNewsKommentar->getID()}" />
                                    </td>
                                    <td>
                                        <strong>
                                            {*{if $oNewsKommentar->cVorname|strlen > 0}*}
                                                {*<label for="nk-{$oNewsKommentar->getID()}">{$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname|truncate:1:""}., {$oNewsKommentar->dErstellt_de}</label>*}
                                            {*{else}*}
                                                <label for="nk-{$oNewsKommentar->getID()}">{$oNewsKommentar->getName()}, {$oNewsKommentar->getDateCreated()->format('d.m.Y H:i')}</label>
                                            {*{/if}*}
                                            <a href="news.php?news=1&kNews={$oNews->getID()}&kNewsKommentar={$oNewsKommentar->getID()}{if isset($cBackPage)}&{$cBackPage}{elseif isset($cTab)}&tab={$cTab}{/if}&nkedit=1&token={$smarty.session.jtl_token}" class="btn btn-default" title="{__("modify")}"><i class="fa fa-edit"></i></a>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>{$oNewsKommentar->getText()}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            {/foreach}
            <div class="btn-group">
                <a class="btn btn-primary" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-angle-double-left"></i> zurück</a>
                <button name="kommentar_loeschen" type="submit" value="{__("delete")}" class="btn btn-danger"><i class="fa fa-trash"></i> {__("delete")}</button>
            </div>
        </form>
    {else}
        <p>
            <a class="btn btn-primary" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-angle-double-left"></i> zurück</a>
        </p>
    {/if}
</div>