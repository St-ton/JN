{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

wunschgemäß haben wir für Sie in unserem Onlineshop unter {$ShopURL}
ein Kundenkonto für Sie eingerichtet.

Zur Kontrolle hier noch einmal Ihre Kundendaten: 

{$Kunde->cAnredeLocalized} {$Kunde->cVorname} {$Kunde->cNachname}
{$Kunde->cStrasse} {$Kunde->cHausnummer}
{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}
{/if}{$Kunde->cPLZ} {$Kunde->cOrt}
{if $Kunde->cBundesland}{$Kunde->cBundesland}
{/if}{$Kunde->cLand}
{if $Kunde->cTel}Telefon: {$Kunde->cTel}
{/if}{if $Kunde->cMobil}Mobil: {$Kunde->cMobil}
{/if}{if $Kunde->cFax}Fax: {$Kunde->cFax}
{/if}Email: {$Kunde->cMail}
{if $Kunde->cUSTID}UstID: {$Kunde->cUSTID}
{/if}
Bitte setzen Sie mit Hilfe der „Passwort vergessen“-Funktion ein neues Passwort.

Mit diesen Daten können Sie sich ab sofort in Ihrem persönlichen
Kundenkonto anmelden und den aktuellen Status Ihrer Bestellungen
verfolgen.

Wir freuen uns sehr, Sie als neuen Kunden bei uns begrüßen zu dürfen.
Wenn sie Fragen zu unserem Angebot oder speziellen Produkten haben,
nehmen Sie einfach Kontakt mit uns auf.

Wir wünschen Ihnen viel Spaß beim Stöbern in unserem Sortiment.

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}