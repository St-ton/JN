{include file='tpl_inc/seite_header.tpl' cTitel=#deleteLinkGroup#}
<div id="content">
    <form method="post" action="links.php">
        {$jtl_token}
        <input type="hidden" name="loesch_linkgruppe" value="1" />
        <input type="hidden" name="kLinkgruppe" value="{$oLinkgruppe->kLinkgruppe}" />

        <div class="alert alert-danger">
            <p><strong>Vorsicht</strong>: Alle Links innerhalb dieser Linkgruppe werden ebenfalls gel&ouml;scht</p>
            {if $affectedLinkNames|count > 0}
                <p>Die folgenden Links werden in anderen Linkgruppen referenziert und werden ebenfalls gelöscht:</p>
                <ul class="list">
                    {foreach $affectedLinkNames as $link}
                        <li>{$link}</li>
                    {/foreach}
                </ul>
            {/if}
            <p>Wollen Sie die Linkgruppe "<strong>{$oLinkgruppe->cName}</strong>" wirklich l&ouml;schen?</p>
        </div>
        <div class="btn-group">
            <input name="loeschConfirmJaSubmit" type="submit" value="{#loeschlinkgruppeYes#}" class="btn btn-danger" />
            <input name="loeschConfirmNeinSubmit" type="submit" value="{#loeschlinkgruppeNo#}" class="btn btn-default" />
        </div>
    </form>
</div>