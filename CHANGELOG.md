# JTL-Shop Changelog

## [4.04.1]
* Neues Premium-Plugin: Login und Bezahlen mit Amazon (von Solution360)
* Neues Premium-Plugin: TrustedShops Trustbade (von AG-Websolutions)
* Update: Google Shopping Plugin v1.05 (Bugfix: Unter Umständen doppelte IDs bei Varkombi-Kindartikeln)
* Bugfix: Testmails werden nur noch auf Deutsch versendet (#241)
* Verbesserung: Vermeiden mehrfacher Cache-Einträge mit demselben Inhalt in gibKategorieFilterOptionen() (#244)
* Bugfix: Mixed-Content-Warnungen (Megamenü-Kategoriebilder via http) bei Teilverschlüsselung und Wechsel auf https (#211)
* Bugfix: Frontendlinks verschwinden aus tseo bei Plugin-Updates in mehrsprachiger Umgebung (#258)
* Bugfix: jtl_token wird sporadisch in der Session überschrieben (#306)
* Bugfix: Boxenverwaltung: Footer für alle Seiten aktivieren geht nicht
* Bugfix: Produktbilder-Encoding bei Dateityp "PNG" fehlerhaft

## [4.04] - 2016-06-22
* Bugfix: robots.txt fehlendes "Sitemap: " vor der Sitemap-URL (#83)
* Bugfix: Billpay Zahlungseingang wird nicht gesetzt (#96)
* Bugfix: Newsletter Abmelden unsichtbar für nicht-angemeldete Besucher (#77)
* Verbesserung: Alte Bildpfade müssen bei Änderung von /bilder/ auf /media/ via 301 weitergeleitet werden (#189)
* Bugfix: Kundenimport Fehler bei unbekannten Spalten (#214)

## [4.03.1] - 2016-05-17
* Bugfix: Sprachwechsel in einigen Linkgruppen unvollständig
* Bugfix: HTTP 500, wenn Object-Cache aktiv ist und Preise erst nach Login sichtbar
* Bugfix: DB-Update läuft in Endlosschleife, wenn das Update ohne Umweg über admin/index.php direkt im Backend angestoßen wird
* Bugfix: reCaptcha-Validierung schlägt bei eingeloggten Kunden fehl
* Bugfix: Konfigurator Initialisierung dauert bei größeren Konfi-Artikeln sehr lange
* Bugfix: Banner werden nicht dargestellt, wenn Aktiv-Von/Bis-Datum fehlt
* Bugfix: Ändern von Kundengruppen-Rabatten invalidiert Objektcache für Artikel und Kategorien nicht
* Bugfix: Thumbnail-Cache-Ordner media/images/product wurde u.U. geleert, obwohl nicht nötig
* Bugfix: Leere Kategorien werden trotz gesetzer Einstellung nicht immer ausgeblendet
* Bugfix: Fehlerhafte Sortierung von Kategorien
* Bugfix: PayPal Basic Transaction ID wird nicht gesetzt
* Bugfix: Artikeldetails "weiter einkaufen" führt zur Startseite
* Bugfix: Fehlerhaftes Routing: /gibtEsNicht/index.php liefert 200 OK statt 404
* Unterstützung: Konfiguratorkomponenten Bildwechsel nutzt Gruppenbild, wenn Komponente kein Bild hat
* Bugfix: Angepasste robots.txt wird falsch sortiert durch robots.php

## [4.03] - 2016-05-09

Dieses Update enthält folgende Verbesserungen und Bugfixes: 

* Bugfix: PayPal PLUS (jtl_paypal 1.04): success_url und cancel_url enthalten html-maskierte &-Zeichen in der URL. Führt bei bestimmten Servereinstellungen zu Fehlern bei Rückleitung zum Shop
* Bugfix: PayPal PLUS (jtl_paypal 1.04): Bei einem gemischten Warenkorb mit verschiedenen Versandklassen wird die Payment Wall nicht geladen
* Bugfix: PayPal PLUS (jtl_paypal 1.04): Shopeigene Zahlungsarten Lastschrift und Kreditkarte werden bei den weiteren PLUS-Zahlungsarten nicht angeboten 
* Verbesserung: PayPal PLUS (jtl_paypal 1.04): Verbesserte interne Prozessbehandlung für Zahlungsarten, die weitere Interaktion mit Kunden voraussetzen (Zahlungszusatzschritt)  
* Verbesserung: PayPal PLUS (jtl_paypal 1.04): Unterstützung für Loading-Indicator eingebaut (Lade-Grafik, während PayPal PLUS Wall lädt)
* Bugfix: PayPal Express (jtl_paypal 1.04): Invoice-ID wird nicht übermittelt (bestellung.cBestellNr)
* Bugfix: PayPal Express (jtl_paypal 1.04): Sporadische Zahlungseingänge ohne tatsächliche PayPal-Zahlung. Zahlung darf nicht gesetzt werden, wenn Paymentstatus != COMPLETED (Sonderfall eCheck, wenn keine Zahlmethode im PayPal-Konto vorhanden ist)
* Verbesserung: PayPal Basic (jtl_paypal 1.04): Weiterleitung zu PayPal erfolgt jetzt erst mit dem Klick auf "Zahlungspflichtig bestellen" (Einstellbar in der Zahlungsart über die Option "Zahlung vor Bestellabschluss") 
* Bugfix: PayPal (jtl_paypal 1.04): State-Parameter für USA, CA, NL und IT werden nicht als ISO-Code an PayPal übermittelt
* Bugfix: PayPal (jtl_paypal 1.04): Negative Variationsaufpreise werden nicht unterstützt
* Bugfix: Billpay: Rechnungsaktivierung aus JTL-Wawi schlägt fehl. Umlaute werden als HTML-Entities dargestellt.
* Bugfix: Anführungszeichen in Plugin-Optionen werden nicht escaped
* Bugfix: Fehlerhafte Plugins (Returncode 90 - doppelte Plugin-ID) tauchen nicht in Liste fehlerhafter Plugins auf
* Bugfix: Variationsaufpreise Live-Berechnung liefert manchmal 1 EUR Gesamtpreis zurück, obwohl ein anderer Preis berechnet werden müsste 
* Bugfix: ReCaptcha wird im Bestellschritt "Registrieren" angezeigt, obwohl Backend-Einstellung "Spamschutz-Methode" auf "Keine" gesetzt ist
* Bugfix: Sprachwechsel-Problem beim Ajax-Nachladen von Varkombis, wenn Variationswerte den gleichen Namen auf englisch und deutsch haben
* Bugfix: Warengruppen werden über Webshopabgleich nicht aktualisiert (Globals-Abgleich)
* Bugfix: Schnellkauf funktioniert unter Umständen nicht
* Bugfix: HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO enthält leere Kunden- und Bestellungsobjekte, wenn über Zahlungsplugin gezahlt wurde
* Bugfix: Duplicate Key bei Einfügen von Lieferscheinpositionen in DB
* Bugfix: Bei Wawi-Kundenänderung darf sich die Login-E-Mail von registrierten Kunden nicht ändern
* Bugfix: Boxen werden nie angezeigt, wenn Filter auf "Eigene Seiten" aktiv ist
* Bugfix: Sitemap-Einträge werden mit SSL-URLs erstellt, wenn automatischer Wechsel zwischen http und https aktiv ist und Export manuell gestart wird
* Bugfix: Änderungen von JS-/CSS-Dateien werden bei Plugin-Update nicht übernommen
* Bugfix: Lokalisierte Plugin-Daten werden bei Sprachwechsel nicht aktualisiert
* Bugfix: Plugin-Boxen bleiben im Frontend aktiv, nachdem Plugin deaktiviert wurde
* Bugfix: Yatego-Export erzeugt Serverfehler
* Bugfix: Statistiken zeigen in bestimmten Zeitabschnitten keine Daten an
* Bugfix: Freischalten von Tags invalidiert Objektcache nicht
* Bugfix: Änderung an Bildoverlays invalidiert Objektcache nicht
* Bugfix: Newsletterempfänger-Import führt zu Serverfehler, wenn CSV-Zeile mit ";" endet
* Bugfix: Spamschutz "Sicherheitscode" funktioniert in alten Tiny-Templates nicht mehr
* Bugfix: Option "Position der Vergleichsliste" ohne Funktion
* Bugfix: Fatal error, wenn Template als "mobil" aktiviert wurde und kein weiteres Desktop-Template vorhanden ist
* Bugfix: Funktionsattribute mit Umlauten verhindern Kauf von Artikeln in Kategorieansicht
* Bugfix: Bei aktiven Sonderpreisen wird bei Vaterartikeln Preis "ab" angezeigt, auch wenn alle Kindartikel den gleichen Preis haben
* Bugfix: Bei vorhandenem Kundengruppen- und Kundenrabatt wird u.U. der niedrigere Rabatt genutzt
* Bugfix: Keine Meta-Keywords in Artikeldetails vorhanden, wenn nicht explizit gesetzt
* Bugfix: U.U. falscher Lagerbestand bei Stücklistenartikeln mit Überverkäufen
* Bugfix: Versandkostenfreigrenze beachtet keine Rabattkupons
* Bugfix: Kupons, die nicht den gesamten Warenkorb rabattieren, erscheinen nicht in Statistik
* Bugfix: 404-Fehler bei Aufruf nicht vollständig lokalisierter Kategorien
* Bugfix: Artikel, die mit Lagerbestand arbeiten und Überverkäufe ermöglichen, werden als "Produkt vergriffen" angezeigt
* Bugfix: Modale Popups im Shop zeigen teilweise "Attention" im Titel an
* Bugfix: Falsches Encoding von Fehlermeldungen bei Wawi-Abgleich
* Bugfix: Links zu Unterkategorien in Kategorieansicht sind nicht lokalisiert
* Bugfix: Ändern von Optionen/Erstellung von bestimmten Inhalten invalidiert nicht alle nötigen Objektcaches
* Bugfix: Wawi-Abgleich invalidiert nicht alle nötigen Objektcaches
* Bugfix: Slider-Slides lassen sich nicht korrekt sortieren
* Bugfix: Auf der Gratisgeschenke-Seite fehlt die Kundengruppensichtbarkeitsprüfung und Prüfung auf Artikelanzeigefilter
* Bugfix: Lagerbestand von Gratisgeschenken wird nicht aktualisiert
* Bugfix: Wunschzettel übernimmt Artikelanzahl nicht
* Bugfix: Staffelpreise werden falsch berechnet, wenn Sonderpreis aktiv ist
* Bugfix: Passwort-Zurücksetzen-Funktion schlägt u.U. fehl, wenn mehrere Kunden mit derselben Email-Adresse existieren
* Bugfix: Hersteller-Links sind nach Sprachwechsel fehlerhaft
* Bugfix: NiceDB::updateRow() gibt im Erfolgsfall stets 1 statt row count zurück
* Bugfix: Newsbeiträge ignorieren Kundengruppensichtbarkeit bei Direktaufruf
* Bugfix: Auf Gratisgeschenke-Seite fehlt Kundengruppensichtbarkeitsprüfung und Prüfung auf Artikelanzeigefilter
* Bugfix: Admin-Logins mit mehr als 20 Zeichen werden gekürzt in DB gespeichert
* Bugfix: Antwortmöglichkeiten im Umfragesystem werden tlw. nicht gespeichert
* Bugfix: Shop-Zurücksetzen-Funktion löscht keine Newsbilder
* Bugfix: Mögliche Kollision unterschiedlicher Funktionen mit Name baueFormularVorgaben
* Verbesserung: Funktion Upload-Löschen für Templates implementiert
* Verbesserung: Backend-Bilder-Einstellung "Containergröße verwenden" wird wieder beachtet (Hintergrundfarbe nur bei JPG, PNG behält transparenten Hintergrund)
* Verbesserung: Profiler unterstützt zusätzlich zu XHProf auch Tideways
* Verbesserung: Möglichkeit, den Template-Cache ohne Plugin direkt im Backend unter "Cache" zu löschen
* Verbesserung: neuer Hook HOOK_KUNDE_DB_INSERT (215), bevor neuer Kunde in DB gespeichert wird
* Verbesserung: Speicheroptimierungen bei Sitemapexport
* Verbesserung: Summenerhaltendes Runden für Warenkorbpositionen (gilt nur für die Einzelpreisanzeige im Warenkorb, Checkout und Bestellbestätigung. Keine Änderung an der Gesamtpreisberechnung oder an Preisen für Wawi-Sync)
* Verbesserung: FlushTags-Attribut in Plugin-XML für automatisches Löschen von Cache-Tags bei Plugin-Installation
* Verbesserung: DokuURL-Attribut in Plugin-XML für Definition eigener Dokumentations-URLs
* Verbesserung: Übersichtlicheres Anlegen von Banner-Zonen
* Verbesserung: E-Mailvorlage Bestellbestätigung: {$Position->cArtNr} ersetzt durch {$Position->Artikel->cArtNr}
* Verbesserung: Einheitliche und erweiterte Filtermöglichkeiten für Banner, Boxen und Slider
* Verbesserung: Performance-Optimierungen bei umfangreichen Kategoriestrukturen und Nutzung des Megamenüs bzw. der Kategoriebox
* Verbesserung: Option für UTF-8 ohne BOM bei Exporten
* Verbesserung: reCaptcha wird bei erfolgreicher Eingabe zukünftig nicht erneut angezeigt
* Verbesserung: Hartkodierte Admin-Pfade entfernt
* Verbesserung: Diverse Konstanten einfacher überschreibbar gemacht
* Verbesserung: .htaccess-Regeln optimiert. robots.txt wird nun dynamisch um die Sitemap-URL ergänzt. Änderungen als Diff: http://jtl-url.de/diffhtaccess402403
* Diverses: Artikel-Weiterempfehlen-Funktion entfernt 
* Diverses: Zahlungsarten Click & Buy und veraltete Saferpay-Integration entfernt. Saferpay empfiehlt https://www.jtl-software.de/Marktplatz/Customweb-Saferpay-292 als Plugin-Lösung für JTL-Shop.  
* Diverses: NiceDB::update() und NiceDB::delete() geben im Fehlerfall nun -1 statt 0 zurück

## [4.02] - 2015-12-18
* Shop-Zahlungsmodul: veraltetes Commerz Finanz-Modul entfernt. Bitte alternativ das von Commerz Finanz empfohlene Plugin für JTL-Shop nutzen.
* Shop-Zahlungsmodul: Komplette Überarbeitung des Billpay-Moduls und Aufteilung in 4 verschiedene Zahlungsarten. WICHTIG: Bitte passen Sie die entsprechenden E-Mail-Vorlagen zu Ihren Billpay-Zahlungsarten bitte an.
* Umbenennung: templates/Evo-Child in templates/Evo-Child-Example umbenannt. Das alte Verzeichnis templates/Evo-Child kann gelöscht werden. 
* Shop-Backend: Newsvorschaubild wird bei erneutem Speichern des Beitrags gelöscht
* Shop-Backend: Kampagnenwert nicht sofort auswählbar und fehlende Bootstrap-Klasse
* Shop-Backend: Newsletter-Smarty-Code wird durch CKEditor zerstört
* Shop-Backend: Newsletter-Smarty-Code wird nicht korrekt geprüft
* Shop-Backend: Linkgruppen mit bestimmten Sonderzeichen sind nicht aufklappbar
* Shop-Backend: Aktionsbuttons bei Emailvorlagen von Plugins wirkungslos
* Shop-Backend: Eingabefelder für Bildgrößen zu klein
* Shop-Backend: Buttonbeschriftung in Bestellungsübersicht falsch
* Shop-Backend: Ändern von Einstellungen invalidiert Objekt-Cache nicht mehr
* Shop-Backend: Mapping von erfolglosen Suchbegriffen nicht mehr möglich
* Shop-Backend: Postleitzahlen in Versandzuschlägen können nur numerisch sein
* Shop-Backend: automatische Generierung der SEO-URLs von CMS-Seiten u.U. fehlerhaft
* Shop-Backend: Freischaltung von Bewertungen aktualisiert u.U. den Bewertungsdurchschnitt von falschen Artikeln und invalidiert den Objektcache nicht
* Shop-Backend: Favicon-Uploader prüft keine Schreibrechte und erwartet Datei immer als favicon.ico
* Shop-Backend: OpCache-Statistik hinzugefügt
* Shop-Backend: Speichern/Anzeigen von PDF-Anhängen in Email-Vorlagen fehlerhaft
* Bibliotheken: PHPMailer auf 5.2.14 aktualisiert
* Shop: Versandart-Staffeln wurden nicht beachtet
* Shop: Deinstallation von Plugins invalidiert wichtige Objektcaches nicht
* Shop: Performance-Optimierung für XSell-Artikel
* Shop: Performance-Optimierung für Kategorielisten
* Shop: Falsche Boxensprache bei Sprachwechsel und aktiviertem Objektcache 
* Shop: Max. Bestellmenge bei Änderungen im Warenkorb wird falsch berechnet
* Shop: Kupons werden doppelt berechnet
* Shop: ChildTemplates ohne eigene Einstellungen sind nicht konfigurierbar
* Shop: Unterkategorien wurden alle als Aktiv gekennzeichnet, wenn Hauptkategorie aktiv war
* Shop: Boxen in Containern werden nicht korrekt dargestellt
* Shop: Hinzufügen/Entfernen von Bildern invalidiert Objekt-Cache nicht
* Shop: Konflikt zwischen Plugin-Boxen und Plugin-Zahlungsarten
* Shop: Probleme mit sehr langen Zeilen in Mails bei manchen MTAs
* Shop: Newsletter-Registrierung auch ohne reCaptcha möglich
* Shop: Fehlerhafte Versandkostenberechnung bei Exporten
* Shop: Kategorien werden bei den Exportformaten nicht exportiert bei weiteren Sprachen
* Shop: Suchweiterleitung bei nur einem Treffer funktioniert nicht
* Shop: Mindestbestellwert von Kupons kann umgangen werden
* Shop: Zuletzt angesehene Artikel werden nur gespeichert, wenn die entsprechende Box auf Produktdetailseiten aktiv ist
* Shop: Breadcrumb-Eintrag bei Vergleichsliste fehlt
* Shop-Update: Sicherstellen, dass Bildeinstellungen für Mini-Produktbilder einen Wert größer 0 gesetzt haben.
* Evo-Editor/LiveStyler: CSS wird bei aktiviertem LiveStyler für Besucher nicht geladen, wenn Minifizierung deaktiviert ist
* JTL-Search: Session geht bei Klick auf Suchvorschlag verloren
* PayPal-Plugin 1.02: Bugfixes: 
    * Kupon wird jetzt nicht mehr als LineItem übertragen sondern (kürzlich erst von PayPal implementiert) als Sonderposition
    * Umlaute bei Zahlungsarten in der Payment-Wall verursachte nicht sichtbaren Fehler, es erfolgte keine Weiterleitung zu PayPal
    * PayPal Lib Update
* PayPal-Plugin 1.03: 
    * Unterstützung von Kauf auf Rechnung in PayPal PLUS! 
    * PayPal Basic ist wieder im Plugin integriert und bildet PayPal Basis-Zahlungen über die API von PayPal ab. 
    * Rundungs-Fix fuer nicht-ganzzahlige Bestellmengen
* Shop: tartikelabnahme.fIntervall in double geaendert (behebt Rundungsfehler bei kundengruppenspezifischen Abnahmeintervallen)
* Shop: Artikel-SEO-URLs, die aus Artikelnamen generiert werden: Querstriche im Namen durch Bindestriche ersetzen (gleiches Verhalten wie im Shop 3)
* Shop: Artikelattributnamen dürfen nun max. 255 Zeichen lang sein (zuvor max 45 Zeichen)
* Canonical auf jeder Seite implementiert, auch wenn es eine Referenz auf die eigene Seite ist - Begruendung siehe https://yoast.com/rel-canonical/
* Geänderte E-Mail-Vorlagen: 
** Bestellung aktualisiert (Satz komplett entfernt: "Die Bestellung wird direkt nach Zahlungseingang versandt.")
** Bestellbestaetigung (Billpay-Anpassungen)



## [4.01] - 2015-10-29
* Shop: Lizenzprüfung vor Update kann u.U. fehlschlagen
* Shop: PHP7-Kompatibilität in NiceDB verbessert
* Shop: pdo bei Installation immer prüfen
* Shop: Umlautprobleme bei CLI-Installer
* Shop: falsche Parameter in HOOK_LETZTERINCLUDE_CSS_JS
* Shop: falsche Smarty-Initialisierung für Seitencache
* Shop: Varkombi-Tausch in auf Tiny basierenden Templates nicht mehr möglich
* Shop: Performance-Verbesserung bei häufigem Ausführen von Hooks
* Shop: Leere Sprachvariablen werden wie nicht-existierende behandelt
* Shop: Bei Lieferadresse=Rechnungsadresse wird stets neue Lieferadresse erstellt
* Shop: Produktanfrage bemängelt fehlende Eingaben
* Shop: Preisgrafik fehlerhaft bei Ziffer 1
* Shop: Zahlung mit Sofortüberweisung nicht möglich
* Shop: Template-Pfade für Plugin-Boxen fehlerhaft
* JTL Search: Access Control-Fehler bei nicht-permanentem SSL
* Shop-Backend: Darstellung von Bildern im CMS-Editor fehlerhaft
* Shop-Backend: Fehlende Grafiken
* Shop-Backend: Umlautfehler in Beschreibungen
* Shop-Backend: SQL-Fehler beim Speichern von Slidern/Slides und Neukundenkupons
* Shop-Backend: SQL-Fehler beim Installieren von Plugins mit Exportformaten
* Shop-Backend: Banner mit Umlauten im Namen erlauben kein Hinzufügen/Bearbeiten von Zonen
* Shop-Backend: ionCube-verschlüsselte Plugins erzeugen unnötige Ausgabe, wenn Extension nicht geladen ist
* Mails: Widerrufsbelehrung ist leer
* ChildTemplate: Theme-Support für ChildTemplates hinzugefügt
* Evo-Editor: ChildTheme-Support hinzugefügt
* Shop: PayPal Plugin Version 1.01: Behoben in dieser Version: PayPal Express taucht als normale Zahlungsart im Checkout auf. Bugfix fuer Zeilenumbrueche in Zahlungsartnamen/Beschreibungen.  
* Shop: PayPal-Standard-Zahlungsart aus JTL-Shop3 reaktiviert. 

## [4.00] - 2015-10-14
### [4.00 Open Beta] - 2015-10-12  Verbesserungen und Bugfixes innerhalb der Open Beta
* Shop: Zahlungsmodul PayPal entfernt. (Als Plugin-Lösung verfügbar)
* Shop: neue PHPMailer Version 5.2.13
* Shop: PHP7-Kompatibilität verbessert
* Shop: Verbesserungen im Zusammenhang mit SQL Strict Mode

### [4.00 Open Beta] - 2015-10-07  Verbesserungen und Bugfixes innerhalb der Open Beta

* Shop: Diverse Fehlermeldungen in dbeS behoben
* Shop: Füge Plugin-Links standardmäßig in Linkgruppe "hidden" ein
* Shop: Explizite Angabe der gewünschten Linkgruppe für Plugins möglich (vgl. Beispiel-Plugin)
* Shop: Darstellung von News-Kommentaren wenn Freischaltung deaktiviert ist
* Shop: Sortierung von Newsbeiträgen die in derselben Minute erstellt wurden korrigiert
* Shop: Hook 99 vor Erstellung der Boxen ausführen, um Modifikationen zu erlauben
* Admin: diverse Verbesserungen in der Sliderverwaltung
* Admin: Aktualisierte Wiki-Links
* Admin: Markup-Fixes in Boxenverwaltung
* Admin: Darstellung langer Log-Einträge verbessert
* Admin: Umlautfehler korrigiert
* Admin: Icons hinzugefügt
* Admin: Falsche Optionen bei Zahlungsart-Plugins mit mehreren Methoden entfernt
* Wawi-Sync Globals: Tabelle twarenlager vor dem Einfuegen neuer Warenlager komplett leeren
* Evo-Editor: Backup von less-Dateien, Zurücksetzen auf Standard behoben
* JTL-Search: Diverse Shop4-Anpassungen
* Bilderschnittstelle: GIF Support, transparentes Watermark (GDLib)

### [4.00 Open Beta] - 2015-10-02 Verbesserungen und Bugfixes innerhalb der Open Beta

* Bugfix: Sonderpreisanzeige in Listen und Boxen fehlerhaft, wenn Endddatum für Sonderpreis auf den aktuellen Tag fällt 
* Objektcache-Methode "mysql" entfernt
* Socket-Support für Installer (behebt Probleme bei Installation auf 1und1-Servern)
* neue Minify-Version
* Billpay Zahlungsabgleich-Informationen: Kontonr und BLZ durch IBAN und BIC ersetzt

### [4.00 Open Beta] - 2015-09-30 - Start der Open Beta (Community Free)

Alle Highlights zum Shoprelease Version 4.00: https://www.jtl-software.de/Onlineshop-Software-JTL-Shop
