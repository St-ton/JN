{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

wir freuen uns Ihnen mitteilen zu d�rfen, dass in unserem Onlineshop folgenden Kupon ({$Kupon->AngezeigterName}) verwenden d�rfen:

{if $Kupon->cKuponTyp=="standard"}Kuponwert: {$Kupon->cLocalizedWert} {if $Kupon->cWertTyp=="prozent"}Rabatt auf den gesamten Einkauf{/if}{/if}{if $Kupon->cKuponTyp=="versandkupon"}Mit diesem Kupon k�nnen Sie versandkostenfrei bei uns einkaufen!
Er gilt f�r folgende Lieferl�nder: {$Kupon->cLieferlaender|upper}{/if}

Kuponcode: {$Kupon->cCode}

G�ltig vom {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} bis {$Kupon->cGueltigBisLong}{/if}

{if $Kupon->fMindestbestellwert>0}Mindestbestellwert: {$Kupon->cLocalizedMBW}

{else}Es gibt keinen Mindestbestellwert!

{/if}{if $Kupon->nVerwendungenProKunde>1}Sie d�rfen diesen Kupon bei insgesamt {$Kupon->nVerwendungenProKunde} Eink�ufen bei uns nutzen.

{elseif $Kupon->nVerwendungenProKunde==0}Sie d�rfen diesen Kupon bei beliebig vielen Eink�ufen bei uns nutzen.

{/if}{if $Kupon->nVerwendungen>0}Bitte beachten Sie, dass dieser Kupon auf eine maximale Verwendungsanzahl hat.

{/if}{if count($Kupon->Kategorien)>0}Der Kupon gilt f�r folgende Kategorien:


{foreach name=art from=$Kupon->Kategorien item=Kategorie}
{$Kategorie->cName} >
{$Kategorie->cURL}
{/foreach}{/if}

{if count($Kupon->Artikel)>0}Der Kupon gilt f�r folgende Artikel:


{foreach name=art from=$Kupon->Artikel item=Artikel}
{$Artikel->cName} >
{$Artikel->cURL}
{/foreach}{/if}

{if is_array($Kupon->Hersteller) && count($Kupon->Hersteller)>0 && !empty($Kupon->Hersteller[0]->cName)}
    Der Coupon gilt für folgende Hersteller:

    {foreach $Kupon->Hersteller as $Hersteller}
        {$Hersteller->cName} >
        {$Hersteller->cURL}
    {/foreach}{/if}

Sie l�sen den Kupon ein, indem Sie beim Bestellvorgang den Kuponcode in das vorgesehene Feld eintragen.

Viel Spa� bei Ihrem n�chsten Einkauf in unserem Shop.

Mit freundlichem Gru�,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}