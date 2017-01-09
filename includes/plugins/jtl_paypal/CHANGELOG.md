# JTL-Shop PayPal Plugin Changelog

## [1.06]

* Authorization-Cache nutzen, um Fehler "too many requests" zu vermeiden (#361)
* Bei sofortigem Negativ-PaymentStatus Rückleitung zur Zahlungsartwahl (Bestellung nicht persistieren!) (#284)
* PayPal PLUS: Zahlungsartname bei Auswahl von Kauf auf Rechnung ändern in "Rechnung (PayPal PLUS)"
* Gratisgeschenke werden nun als Item gelistet
* PayPal Express: Einstellung "Kunde soll ein Kundenkonto erhalten" entfernt (#427)
* PayPal Basic: Guthaben nutzen leitet auf Zahlungsart-Auswahl (#313)
* PayPal Basic: Invalidierung des Warenkorbs bei Abbruch der Zahlung in PayPal und Rückleitung zum Shop (#103)
* PayPal Basic: TrustedShops Excellence Käuferschutzgebühr wird nun an PayPal übertragen (#315)
* PayPal Basic Bestellungen lassen sich nun nachträglich bezahlen (#74)
* PayPal PLUS: Third Party Zahlungsarten werden nun unterhalb der Payment Wall gelistet (keine Limitierung mehr)
* PayPal PLUS Payment Wall Style-Support (neue Einstellungen)
* PayPal PLUS: Bei Kauf auf Rechnung soll keine Zahlungsbestätigungsmail gesendet werden (#618)
* Bugfix: TLS-Check lieferte teilweise falsche Ergebnisse, da Version nicht festgelegt war
* Bugfix: Einlösen eines Kupons in Kombination mit der Zahlungsart PayPal Basic nicht möglich (#373)
* Bugfix: PayPal PLUS: Bestellnummer wird nicht korrekt an PayPal übersendet (#437)
* Bugfix: Bundesland (Freitext) wird nicht an PayPal PLUS übergeben
* Bugfix: PayPal PLUS: Rundungsdifferenzen bei mehr als 2 Nachkommastellen werden nicht ausgeglichen ("validation error") (#317)
* Bugfix: Rundungsfehler bei PayPal mit Kuponnutzung (#272)
* Bugfix: PayPal PLUS Summenfehler: Einzelpositions-Wert ergibt nicht Gesamtsumme (#339)
* Bugfix: PayPal Express Plugin setzt UStId auf NOVATID, obwohl Feld optional ist
* Bugfix: PayPal Express Button wird nicht im Warenkorb gezeigt
* Bugfix: PayPal IPNs loggen einen Fehler, wenn die Bestellung bereits bezahlt wurde (#24)
* Bugfix: PayPal Express: Vor-/Nachname werden inkorrekt ermittelt (#669)
* Bugfix: Validation Error bei Lieferland Mexico (field state missing) (#779)
