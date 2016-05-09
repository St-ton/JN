{include file='layout/header.tpl'}
<h1>{$CWunschliste->cName}{if isset($CWunschliste->oKunde->cVorname)} {lang key="from" section="product rating"} {$CWunschliste->oKunde->cVorname}{/if}</h1>

{if !empty($cHinweis)}
    <p class="alert alert-success">{$cHinweis}</p>
{/if}

{include file="snippets/extension.tpl"}

{if isset($CWunschliste->CWunschlistePos_arr) && $CWunschliste->CWunschlistePos_arr|@count > 0}
    <input type="hidden" name="wla" value="1" />
    <input type="hidden" name="wl" value="{$CWunschliste->kWunschliste}" />
    <table class="table table-striped">
        <thead>
        <tr>
            <th></th>
            <th>{lang key="wishlistPosCount" section="login"}</th>
            <th>{lang key="wishlistProduct" section="login"}</th>
            <th>{lang key="wishlistComment" section="login"}</th>
            <!--<th>{lang key="wishlistAddedOn" section="login"}</th>-->
        </tr>
        </thead>
        <tbody>
        {foreach name=wunschlistepos from=$CWunschliste->CWunschlistePos_arr item=CWunschlistePos}
            <tr>
                <td style="width:10%">
                    <a href="{$CWunschlistePos->Artikel->cURL}"><img src="{$CWunschlistePos->Artikel->Bilder[0]->cPfadKlein}" class="image"></a>
                </td>
                <td><b>{$CWunschlistePos->fAnzahl}</b><br>{$CWunschlistePos->Artikel->cEinheit}</td>
                <td valign="middle">
                    <a href="{$CWunschlistePos->Artikel->cURL}">{$CWunschlistePos->cArtikelName}</a>
                    <p><span class="price">{$CWunschlistePos->cPreis}</span></p>
                    {foreach name=eigenschaft from=$CWunschlistePos->CWunschlistePosEigenschaft_arr item=CWunschlistePosEigenschaft}
                        {if !empty($CWunschlistePosEigenschaft->cEigenschaftName) && !empty($CWunschlistePosEigenschaft->cEigenschaftWertName)}
                            <p>
                                <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                {$CWunschlistePosEigenschaft->cEigenschaftWertName}
                            {if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}</p>{/if}
                        {/if}
                    {/foreach}
                </td>
                <td valign="middle">{$CWunschlistePos->cKommentar}</td>
                <!--<td valign="top">{$CWunschlistePos->dHinzugefuegt_de}</td>-->
            </tr>
        {/foreach}
        </tbody>
    </table>
{else}
    {if !empty($cFehler)}
        <br>
        <div class="alert alert-danger">
            {$cFehler}
        </div>
    {/if}
    <br>
{/if}

{include file='layout/footer.tpl'}