Breaking Changes
================

.. |rarr| raw:: html

   &rArr;

.. |br| raw:: html

   <br />

JTL-Shop 4.x |rarr| JTL-Shop 5.x
--------------------------------


- **Systemvoraussetzung auf PHP 7.2 angehoben**

    Für den Betrieb von JTL-Shop 5.x ist PHP 7.2 Voraussetzung.

- **jQuery Version auf 3.0 angehoben**

    Mit JTL-Shop 5.x erfolgte für das Javascript-Framework *jQuery* ein Update von Version 1.12 auf
    Version 3.0. |br|
    Weitere Informationen hierzu finden Sie im Abschnitt ":doc:`/shop_templates/jquery_update`".

- **Bootstrap-Version auf 4.1 angehoben**

    Das CSS-Framework *Bootstrap* hat mit JTL-Shop 5.x ebenfalls ein Update erhalten und ist nun in Version 4.1.3
    im Onlineshop enthalten.

- **Die veralteten Bibliotheken "xajax" und "PclZip" wurden entfernt**

- **Die ungenutzte Seitentypen "Newsarchiv" und "RMA" wurden entfernt**

- **Versionierung geändert: "Semantic Versioning" für den Onlineshop, "API-Versioning" intern für den Abgleich mit
  JTL-Wawi**

    Mit JTL-Shop 5.x wird die Versionsnummerierung des Onlineshops auf das allgemein gültige Verfahren
    `SemVer <http://semver.org/>`_ umgestellt. |br|
    Für die Verbindung zur JTL-Wawi wird intern weiterhin die bisherige Versionierung als interne API-Version geführt.

- **UTF8-Migration für gesamten Onlineshop**

    + Zur String-Manipulation werden die PHP *Multibyte String Funktionen* (``mb_``) empfohlen.
    + Die Funktion ``utf8_encode()`` sollte in Plugins nicht mehr eingesetzt werden.
    + Die Datenbank von JTL-Shop 5.x  wurde hinsichtlich ihrer Kollationen und der jeweiligen Tabellen-Engines
      überarbeitet und auf UTF8 umgestellt. |br|

      +-------------------+---------------------+
      | default collation | ``utf8_unicode_ci`` |
      +-------------------+---------------------+
      | default engine    | ``InnoDB``          |
      +-------------------+---------------------+

- **Darstellung und Erzeugung der Menüstruktur im Backend wurde geändert**

    Die dynamische Erzeugung der Menüstruktur des Backends wurde überarbeitet. Sie basiert ab JTL-Shop 5.0 nicht mehr
    auf Datenbanktabellen, sondern auf der Struktur in der Datei ``admin/includes/admin_menu.php``. |br|
    Die Anordnung sämtlicher Menüeinträge wurde im Zuge dieser Änderung ebenfalls stark modernisiert.

- **Mehrsprachigkeit des Backends auf "GNU gettext" umgestellt**

    Die Mehrsprachigkeit aller Menüs im Backend des Onlineshops wird ab JTL-Shop 5.0 mittels
    `GNU gettext <https://www.gnu.org/software/gettext/>`_ geregelt.

- **Plugins werden nicht mehr im Installationspaket ausgeliefert**

    Plugins werden zukünftig über den JTL-Store installierbar sein. |br|
    Aus diesem Grund sind im Installationspaket des Onlineshops keine Plugins mehr enthalten.

- **Werkzeuge zum Kompilieren von Themes überarbeitet**

    Zum Kompilieren eines eigenen Themes dient in JTL-Shop 4.x der
    `Evo Editor <https://gitlab.com/jtl-software/jtl-shop/legacy-plugins/evo-editor>`_ |br|
    In JTL-Shop 5.x werden Themes mit dem
    `JTL Theme Editor <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_theme_editor>`_ übersetzt

    Weitere Informationen zur Verwendung dieser Plugins finden Sie im Abschnitt ":ref:`label_eigenestheme_kompilieren`".

- **Von inländischer auf europaweite USt-ID-Prüfung umgestellt**

    Die bisherige Lösung zur Prüfung der Umsatzsteuer-ID (gültig nur für Deutschland) wurde ersetzt durch die
    EU-weite Prüfung durch das Mehrwertsteuer-Informationsaustauschsystem MIAS der Europäischen Union.

    Weitere Informationen zu diesem System finden Sie unter:
    `MIAS der EU <https://europa.eu/youreurope/business/taxation/vat/check-vat-number-vies/index_de.htm>`_

- **Tabelle `tpreise`, inkl. deren Befüllung durch dbeS, entfernt**

    In JTL-Shop 4.x werden aus Kompatibilitätsgründen zu JTL-Wawi 0.9 Preise redundant in mehreren Tabellen der
    Datenbank gehalten (``tpreise`` und ``tpreis``/``tpreisdetail``). |br|
    Diese doppelte Datenhaltung wurde in JTL-Shop 5.x entfernt. Alle Daten zu Preisen befinden sich nun ausschließlich
    in den Tabellen ``tpreis`` und ``tpreisdetail``.

- **Einstellung "Finanzierungsvorschlag zeigen" (1324) entfernt**

    Diese Einstellung wurde mit JTL-Shop 3.x im Rahmen des Finanzierungsmoduls "Dresdner Cetelem" / "Commerz Finanz"
    angelegt. Sie ist in JTL-Shop 4.x und JTL-Shop 5.x nicht mehr enthalten.

- **Der Auswahlassistent wird zum Portlet**

    Das bisher unter JTL-Shop 4.x vorhandene Modul "Auswahlassistent" wurde aus dem Core von JTL-Shop entfernt.

    Der Auswahlassistent ist nun ein Portlet des OnPage Composers für JTL-Shop 5.x. |br|
    Dadurch lassen sich im OnPage Composer Auswahlassistenten auf der jeweiligen Seite platzieren.

- **Datentyp für Mediendateien Tabs geändert**

    Die Artikeleigenschaft ``$cMedienTyp_arr`` ist in JTL-Shop 5.x nun ein Array von Arrays und nicht mehr wie bisher
    ein assoziatives Array.

- **Mehrere Zahlungsart-Integrationen wurden entfernt**

    Die folgenden Zahlungsmodule werden von JTL im Auslieferungszustand von JTL-Shop nicht mehr angeboten und wurden
    aus dem Core von JTL-Shop 5.x entfernt: |br|

    - EOS
    - Wirecard
    - UT
    - ipayment
    - PaymentPartner
    - PostFinance
    - SafetyPay
    - WorldPay
    - Sofort
    - Billpay
    - Moneybookers
    - UOS

    Die Zahlungsart "PayPal" wird zukünftig ebenfalls als Plugin *JTL PayPal* angeboten.

- **Hooks erweitert/ergänzt/entfernt**

    Im Zuge der hier genannten Anpassungen und Änderungen haben sich auch verschiedene Hooks des Plugin-Systems
    geändert, wurden ergänzt oder sind ganz weggefallen. |br|
    Eine komplette Liste aller aktuell verfügbaren Hooks und ihrer Parameter finden Sie hier in der
    Entwicklerdokumentation unter ":doc:`/shop_plugins/hook_list`".

- **Die "Imanee Image Manipulation Lib" wurde entfernt**

    Das Imanee-Projekt zur Bildbearbeitung wird vom Betreiber seit mehreren Jahren nicht mehr gewartet und wurde nun
    aus dem Core von JTL-Shop 5.x entfernt.

- **Das Feature "Produkt-Tags" wurde entfernt**

    Das Produkttagging durch Kunden wird wenig genutzt und ist nicht mehr zeitgemäß. |br|
    Dieses Feature wurde mit JTL-Shop 5.x aus dem Core von JTL-Shop entfernt.

- **Die URL-Generierung wurde überarbeitet**

    SEO-URLs werden nicht mehr mit der alten Funktion ``iso2ascii()`` behandelt, sondern erhalten zentralisiert
    im SEO-Helper ein eigenes Prüf- und Kodierverfahren.

- **Die Einstellungen (1142) und (1130) für die Anzahl der Vorschaubilder bei Varkombis wurde entfernen**

    Aufgrund der performanteren Darstellung der Artikeldetails im NOVA-Template sind diese beiden Einstellungen
    überholt und wurden mit JTL-Shop 5.x aus dem Core von JTL-Shop entfernt.

- **Das Duplizieren von Bildern in Multilanguage-Onlineshops wurde deaktiviert**

    In multilingualen Onlineshops wurden bisher alle Artikelbilder pro Sprache erzeugt und geladen. Dieser Overhead an
    Rechenzeit und Datentransfer wird in JTL-Shop 5.x relativiert, indem nur noch ein Bildersatz in der Standardsprache
    vorgehalten wird. |br|
    Die fremdsprachigen Bildnamen-Attribute aus JTL-Wawi werden nicht länger berücksichtigt, da diese Attribute
    nur verbalen Charakter besitzen. JTL-Wawi speichert ebenfalls nur einen Bildersatz für die Standardsprache.

- **Bilderverzeichnisse wurden zusammengefasst**

    Die bisherigen Speicherorte für Bilder und Mediendateien (``media/``, ``mediafiles/``, ``gfx/``) wurden unter dem
    Verzeichnis ``bilder/`` zusammengeführt. |br|
    Die bisherigen Verzeichnisse werden nicht mehr genutzt.

- **Veraltete Module wurden entfernt**

    Folgende veraltete Module wurden aus dem Core von JTL-Shop entfernt:

    - Preisradar
    - Preisgrafik
    - Umfrage

- **Das Widget "Do You Know" ("DUK") wurde entfernt**

    Diese Features wurden bisher sehr wenig genutzt und sind nicht mehr zeitgemäß. |br|
    Sie wurden mit JTL-Shop 5.x aus dem Core von JTL-Shop entfernt.

- **Die dynamische Preisberechnung erlaubt nun gleichbleibende Preise bei Auslandslieferungen**

    Die dynamische Berechnung der Nettopreise wurde in JTL-Shop 5.x in die default-Einstellungen übernommen.

    In JTL-Shop 4.x kann diese Berechnung mittels Konfigurationseinstellung in der
    ``includes/config.JTL-Shop.ini.php`` aktiviert werden:

    .. code-block:: php

       define('CONSISTENT_GROSS_PRICES', true);

- **Das Yatego-Exportformat wurde entfernt**

    Das veraltete und fehlerhafte Exportformat "Yatego" wurde aus dem Core von JTL-Shop entfernt.

    Zukünftig wird dieses Exportformat ggf. von Yatego selbst als Plugin zur Verfügung gestellt.

- **Exportformate von Drittanbietern entfernt:**

    Folgende Exportformat von Drittanbietern wurden aus dem Core von JTL-Shop entfernt:

    - Hardwareschotte
    - Kelkoo
    - Become
    - Europe

- **Im meta-Tag "robots" von Spezialseiten ist der "content" nun auf "nofollow, noindex" gesetzt**

    Aus SEO-Sicht bringt die Indexierung dieser Seitentypen keinen Mehrwert. |br|
    Liegen hier zudem Fehler in den Rechtstexten vor, kann eine Indexierung dazu führen, dass diese Seiten von
    Abmahn-Anwälten per Google-Suche leicht gefunden werden.

    Die Spezialseiten wurden daher in JTL-Shop 5.x im meta-Tag-Parameter "content" auf "nofollow, noindex" gesetzt.

- **Schnellere Versandarten werden priorisiert**

    Ab JTL-Shop 5.x werden Versandarten nicht nur nach ihrem Preis sortiert angezeigt. |br|

    Beispielsweise wird nun bei zwei Versandarten mit gleichem Preis die Versandart mit der niedrigeren
    Sortiernummer (entspricht höherer Priorität) vor der Versandart mit höherer Sortiernummer angezeigt. |br|
    Somit können Versandarten mit schnellerem Versand in der Versandartenliste höher eingeordnet werden.

- **Konsistenzprüfung im Warenkorb**

    Ab JTL-Shop 4.05 wird mit Hilfe einer Checksumme eine Konsistenzprüfung im Warenkorb durchgeführt. |br|
    Weitere Informationen hierzu finden im Abschnitt ":ref:`label_hinweise_wkchecksum`".

- **Die favicon-Uploadfunktionalität wurde überarbeitet**

    Mit JTL-Shop 5.x wurde die Uploadfunktionalität für das Onlineshop-*favicon* überarbeitet.

    Die folgenden Pfade zeigen die Verzeichnisse, in denen nach dem favicon gesucht wird: |br|
    (in der Reihenfolge von oben nach unten)

    * Frontend:

    .. code-block:: console

       [Shop-root]/[Templates-Pfad]/themes/base/images/favicon.ico
       [Shop-root]/[Templates-Pfad]/favicon.ico
       [Shop-root]/favicon.ico
       [Shop-root]/favicon-default.ico

    * Backend:

    .. code-block:: console

       [Shop-root]/[admin-Pfad]/favicon.ico
       [Shop-root]/[admin-Pfad]/favicon-default.ico

    Sobald in einem der Pfade ein *favicon* gefunden wird, wird die Suche beendet und das gefundene *favicon*
    verwendet.

- **Google Analytics Tracking wurde aus dem Core von JTL-Shop entfernt**

    Aufgrund umfangreicher Änderungen in "Google Analytics" wurde die bisher im Onlineshop verwendete Implementierung
    (``ga.js``) aus JTL-Shop 5.x entfernt.

    Zukünftig wird es für das Tracking gesonderte Plugins geben, die auch die aktuellen Anforderungen der DSGVO
    umsetzen.

- **Google-Recaptcha und Gravatar wurden aus dem Core von JTL-Shop entfernt**

    Gemäß den Anforderungen der DSGVO müssen für die Datenweitergabe an Drittanbieter jeweils gesonderte
    Einverständnisse von allen Endkunden eingeholt werden. Deshalb wurden diese Drittanbietermodule aus JTL-Shop 5.x
    entfernt.

    JTL-Shop wird standardmäßig so ausgeliefert, dass keine Datenweitergabe an Drittanbieter stattfindet.

- **DSGVO-Konformität hergestellt**

    Mit Inkrafttreten der DSGVO wurden im Onlineshop mehrere Anpassungen vorgenommen.

    Das Einholen der Einverständniserklärung von Endkunden für marketingrelevante E-Mails wird nun durch ein neues
    Double-OptIn-Interface (siehe ``includes/src/Optin/``) abgedeckt. |br|
    Weiterhin wurde in JTL-Shop 5.x eine Bereinigung bzw. Verschlüsselung von personenbezogenen Daten von Endkunden
    implementiert (siehe ``includes/src/GeneralDataProtection/``), die regelmäßig über Chronjobs getriggert wird.

- **Kryptografische Funktionen überarbeitet**

    Kryptografische Funktionen wie auch Funktionen zur Generierung von IDs sind stark auf die Erzeugung von
    Zufallszahlen angewiesen, welche nicht immer wirklich zufällig sind, sobald sie maschinell erzeugt werden. |br|
    Die PHP-Standardfunktionen zur Erzeugung von Zufallszahlen sind hier keine Ausnahme. |br|

    Um diesem Problem wirkungsvoll zu begegnen, wurden entsprechend verbesserte Bibliotheken zur Erzeugung von
    Zufallszahlen in JTL-Shop 5.x integriert.

    Diese Überarbeitung der kryptografischen Funktionen des Onlineshops bedingte ebenso einen Austausch der
    Hashing-Funktionen, die vor dem Speichern von Passworten aufgerufen werden.

