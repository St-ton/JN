{includeMailTemplate template=header type=html}

{function quantityStatisticRow}
    {if $nAnzahlVar !== -1}
        <div style="display:table-row">
            <div style="display:table-cell;padding:0.5em;text-align:right;">
                {$cAnzahlTitle}
            </div>
            <div style="display:table-cell;padding:0.5em;">
                {$nAnzahlVar}
            </div>
        </div>
    {/if}
{/function}

<h1>{$oMailObjekt->cIntervall}</h1>
<h2>Period: {$oMailObjekt->dVon} - {$oMailObjekt->dBis}</h2>

<div style="display:table">
    {if is_array($oMailObjekt->oAnzahlArtikelProKundengruppe)}
        {foreach $oMailObjekt->oAnzahlArtikelProKundengruppe as $oArtikelProKundengruppe}
            {quantityStatisticRow cAnzahlTitle='Products per customer group: '|cat:$oArtikelProKundengruppe->cName nAnzahlVar=$oArtikelProKundengruppe->nAnzahl}
        {/foreach}
    {/if}

    {quantityStatisticRow cAnzahlTitle='New customers' nAnzahlVar=$oMailObjekt->nAnzahlNeukunden}
    {quantityStatisticRow cAnzahlTitle='New customers who purchased something' nAnzahlVar=$oMailObjekt->nAnzahlNeukundenGekauft}
    {quantityStatisticRow cAnzahlTitle='Orders' nAnzahlVar=$oMailObjekt->nAnzahlBestellungen}
    {quantityStatisticRow cAnzahlTitle='Orders from new customers' nAnzahlVar=$oMailObjekt->nAnzahlBestellungenNeukunden}
    {quantityStatisticRow cAnzahlTitle='Paid orders' nAnzahlVar=$oMailObjekt->nAnzahlZahlungseingaengeVonBestellungen}
    {quantityStatisticRow cAnzahlTitle='Shipped orders' nAnzahlVar=$oMailObjekt->nAnzahlVersendeterBestellungen}
    {quantityStatisticRow cAnzahlTitle='Visitors' nAnzahlVar=$oMailObjekt->nAnzahlBesucher}
    {quantityStatisticRow cAnzahlTitle='Visitors from search engines' nAnzahlVar=$oMailObjekt->nAnzahlBesucherSuchmaschine}
    {quantityStatisticRow cAnzahlTitle='Ratings' nAnzahlVar=$oMailObjekt->nAnzahlBewertungen}
    {quantityStatisticRow cAnzahlTitle='Non-public ratings' nAnzahlVar=$oMailObjekt->nAnzahlBewertungenNichtFreigeschaltet}

    {if isset($oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben) && isset($oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl)}
        {quantityStatisticRow cAnzahlTitle='Rating credit paid' nAnzahlVar=$oMailObjekt->oAnzahlGezahltesGuthaben->nAnzahl}
        {quantityStatisticRow cAnzahlTitle='Rating credit total' nAnzahlVar=$oMailObjekt->oAnzahlGezahltesGuthaben->fSummeGuthaben}
    {/if}

    {quantityStatisticRow cAnzahlTitle='Tags' nAnzahlVar=$oMailObjekt->nAnzahlTags}
    {quantityStatisticRow cAnzahlTitle='Tags not approved' nAnzahlVar=$oMailObjekt->nAnzahlTagsNichtFreigeschaltet}
    {quantityStatisticRow cAnzahlTitle='Acquired customers' nAnzahlVar=$oMailObjekt->nAnzahlGeworbenerKunden}
    {quantityStatisticRow cAnzahlTitle='Acquired customers who purchased something' nAnzahlVar=$oMailObjekt->nAnzahlErfolgreichGeworbenerKunden}
    {quantityStatisticRow cAnzahlTitle='Wish lists sent' nAnzahlVar=$oMailObjekt->nAnzahlVersendeterWunschlisten}
    {quantityStatisticRow cAnzahlTitle='Surveys conducted' nAnzahlVar=$oMailObjekt->nAnzahlDurchgefuehrteUmfragen}
    {quantityStatisticRow cAnzahlTitle='New article comments' nAnzahlVar=$oMailObjekt->nAnzahlNewskommentare}
    {quantityStatisticRow cAnzahlTitle='Article comments not published' nAnzahlVar=$oMailObjekt->nAnzahlNewskommentareNichtFreigeschaltet}
    {quantityStatisticRow cAnzahlTitle='New product questions' nAnzahlVar=$oMailObjekt->nAnzahlProduktanfrageArtikel}
    {quantityStatisticRow cAnzahlTitle='New availability questions' nAnzahlVar=$oMailObjekt->nAnzahlProduktanfrageVerfuegbarkeit}
    {quantityStatisticRow cAnzahlTitle='Product comparisons' nAnzahlVar=$oMailObjekt->nAnzahlVergleiche}
    {quantityStatisticRow cAnzahlTitle='Coupons used' nAnzahlVar=$oMailObjekt->nAnzahlGenutzteKupons}
</div>

{if isset($oMailObjekt->oLogEntry_arr)}
    <h2>Log entries ({$oMailObjekt->oLogEntry_arr|@count}):</h2>
    {foreach $oMailObjekt->oLogEntry_arr as $oLogEntry}
        <h3>
            [{$oLogEntry->dErstellt|date_format:"%d.%m.%Y %H:%M:%S"}]
            {if $oLogEntry->nLevel == 1}
                <span style="color:#f00;">[Error]</span>
            {elseif $oLogEntry->nLevel == 2}
                <span style="color:#00f;">[Notice]</span>
            {elseif $oLogEntry->nLevel == 4}
                <span style="color:#fa0;">[Debug]</span>
            {/if}
        </h3>
        <pre>{$oLogEntry->cLog}</pre>
    {/foreach}
{/if}

{includeMailTemplate template=footer type=html}