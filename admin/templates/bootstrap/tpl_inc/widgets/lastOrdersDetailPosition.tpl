<tr>
    <td>
        {if strlen($Position->cUnique) > 0 && $Position->kKonfigitem !== 0}
            {$Position->nAnzahl}
        {/if}
    </td>
    <td>
        {if $Position->nPosTyp === $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL}
            <a class="text-decoration-underline" href="{$shopURL}/index.php?a={$Position->kArtikel}" target="_blank">{$Position->cName}</a>
            {if strlen($Position->cUnique) > 0 && $Position->kKonfigitem === 0}
                <table class="ml-3 table">
                    {foreach from=$Bestellung->Positionen item=KonfigPos}
                        {if $Position->cUnique == $KonfigPos->cUnique}
                            <tr>
                                <td>
                                    {if !(strlen($KonfigPos->cUnique) > 0 && $KonfigPos->kKonfigitem == 0)}{$KonfigPos->nAnzahlEinzel}x {/if}{$KonfigPos->cName}
                                </td>
                                <td>
                                    <span class="price">{$KonfigPos->cEinzelpreisLocalized[1]}</span>
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                </table>
            {/if}
            {if count($Position->WarenkorbPosEigenschaftArr) > 0}
                <table class="mt-3 ml-4 table">
                {foreach $Position->WarenkorbPosEigenschaftArr as $WKPosEigenschaft}
                    <tr>
                        <td>{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}</td>
                        <td>
                            {if $WKPosEigenschaft->fAufpreis}
                                {$WKPosEigenschaft->cAufpreisLocalized[1]}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </table>
            {/if}
        {else}
            {$Position->cName}
            {if strlen($Position->cHinweis) > 0}
                <p>
                    <small>{$Position->cHinweis}</small>
                </p>
            {/if}
        {/if}
    </td>
    <td>
        {if strlen($Position->cUnique) > 0 && $Position->kKonfigitem == 0}
            {$Position->cKonfigeinzelpreisLocalized[1]}
        {else}
            {$Position->cEinzelpreisLocalized[1]}
        {/if}
    </td>
</tr>
