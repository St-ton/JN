Shop CLI
========

.. |br| raw:: html

    <br />


Die Shop CLI ist ein Kommandozeilen-Tool, welches mit dem PHP-Kommandozeileninterpreter
verwendet wird und die Möglichkeit bietet, administrative Aufgaben ohne Shop-Backend
auszuführen. |br|
Um die Shop CLI verwenden zu können, muß PHP als Kommandozeileninterpreter verfügbar sein.
(siehe: PHP-Konfiguration des jeweiligen Servers)


Aufruf der Shop CLI
-------------------

Die Shop CLI wird Im Hauptverzeichnis (Installationsverzeichnis) von JTL-Shop aufgerufen:

.. code-block:: text

    $> php cli [befehl:sub-befehl [parameter]]

Ein Aufruf ohne Befehle, wie auch der Aufruf mit dem Befehl ``list``, gibt eine kurze
Liste aller verfügbaren Befehlen aus.

.. code-block:: text

    $> php cli
    ...
    $> php cli list
    ...

Mehrere Befehle haben nur einen Sub-Befehl. |br|
Werden solche Befehle angegeben, fragt die Shop CLI interaktiv, ob der eine Sub-Befehl ausgeführt werden soll.


Hilfe zu Befehlen
-----------------

Mit dem Befehl ``help`` vor einem Befehl, wie auch mit den Parametern ``-h`` und ``--help`` nach
einem Befehl, erhält man Hilfe zu diesem spezifischen Befehl.

.. code-block:: text

    $> php cli help generate:demodata
    ...
    $> php cli generate:demodata -h
    ...
    $> php cli generate:demodata --help
    ...


Die Befehle im Einzelnen
------------------------

``migrate``
...........

Wird der Befehl ``migrate`` ohne Sub-Befehl aufgerufen, so führt er alle Migrationen aus,
die bis zum aktuellen Zeitpunkt noch nicht ausgeführt wurden.

``migrate:create``
..................

Erzeugt den Objektrumpf einer neuen Migration. |br|
Diese neue Migration enthält zwei leere Methoden (``up()``, ``down()``), die vom Entwickler zu implementieren sind.

``migrate:innodbutf8``
......................

Führt InnoDB- und UTF8-Migrationen aus.

``migrate:status``
..................

Gibt eine Liste aller Migrationen und deren Ausführungsstatus aus.

backup
......

Der Befehl ``backup`` ist nicht alleinstehend aufrufbar. Er kann nur mit einem spezifischen Unterbefehl aufgerufen
werden.

``backup:db``
.............

``db`` erzeugt ein Backup der Shop-Datenbank. |br|
Das erzeugte Backup wird unter ``export/backup/[DatumID]_backup.sql`` gespeichert. |br|
Mit dem Parameter ``-c`` (oder ``--compress``) kann die Backupdatei mit *gzip* gepackt werden. Der Dateiname
wird dann zu ``export/backup/[DatumID]_backup.sql.gz``.

``backup:files``
................

``files`` erzeugt ein Backup der Ordner- und Dateistruktur des Shops. |br|
Mit dem Parameter ``--exclude-dir=`` können ein oder mehrere Verzeichnisse vom Archiviervorgang ausgeschlossen
werden. |br|
(Bei mehreren Verzeichnissen wird der exclude-Parameter
auch mehrmals benutzt: ``exclude-dir=pfad_a --exclude-dir=pfad_b`` usw.)

.. danger::

    Sollte es sich beim Installationsverzeichnis um ein git-Repository handeln, ist es ratsam,
    das ``.git/``-Verzeichnis immer mit ``--exclude-dir=.git/`` vom Archivieren auszuschließen!

.. caution::

    Sehr große Verzeichnisse (zum Beispiel: Bilderverzeichnisse, ggf. ``includes/vendor/``) solltem, nach Möglichkeit,
    beim Archivieren weggelassen werden, da der Vorgang sonst sehr lange dauern kann.

Die erzeugte ``.zip``-Datei wird unter ``export/backup/[DatumID]_file_backup.zip`` gespeichert.


cache
.....

Der Befehl ``cache`` ist nicht alleinstehend aufrufbar. Er kann nur mit einem spezifischen Unterbefehl aufgerufen
werden.

``cache:dbes:delete``
.....................

löscht temporäre dbeS-Dateien (normalerweise automatisch, Konstante kann dies verhindern KEEP_SYNC_FILES)

``cache:file:delete``
.....................

alles in templates_c/filecache/ ... (wenn Objekt-Cache-Methode "Dateien" und "Dateien (erweitert)" (Methoden-Namen?) gesetzt ist)

``cache:tpl:delete``
....................

alles in templates_c/NOVA/ (für aktives Template)

``cache:clear``
...............

der OBJ-Cache der aktuell aktiven Methode

``cache:warm``
..............


compile
.......

Der Befehl ``compile`` ist nicht alleinstehend aufrufbar. Er kann nur mit einem spezifischen Unterbefehl aufgerufen
werden.

``compile:less``
................

Übersetzt alle ``.less``-Dateien im JTL-Shop.

``compile:sass``
................

Übersetzt alle ``.sass``-Dateien im JTL-Shop.


generate
........

Der Befehl ``generate`` kann alleinstehend aufgerufen werden, fragt aber dann interaktiv, ob der einzige
Sub-Befehl aufgerufen werden soll.

``generate:demodata``
.....................

Demodaten sind einfache Artikel und Kategorien, die dieser Befehl anlegen kann, um die allgemeine Funktionalität
des JTL-Shop zu demonstrieren.


mailtemplates
.............

Der Befehl ``mailtemplates`` kann alleinstehend aufgerufen werden, fragt aber dann interaktiv, ob der einzige
Sub-Befehl aufgerufen werden soll.

``mailtemplates:reset``
.......................

Alle Mailtemplates des JTL-Shop sind vom Shopbetreiber frei konfigurierbar. Sie werden in der Datenbank
gespeichert. |br|
Um diese Mailtemplates wieder auf ihren Auslieferungszustand zu setzen, kann dieser Befehl verwendet werden.


model
.....

Der Befehl ``model`` kann alleinstehend aufgerufen werden, fragt aber dann interaktiv, ob der einzige
Sub-Befehl aufgerufen werden soll.

``model:create``
................

Dieser Befehl kann interaktiv aufgerufen werden. |br|
Er erzeugt eine neue Klasse, abgeleitet von ``DataModel``, mit dem Namen ``T[Tabellenname]Model.php``,
welche die angegebene Tabelle abbildet.

.. caution::

    Zum Speichern der neuen Objekte muß ein Ordner names ``models/`` im Hauptverzeichnis des Shops vorhanden und von
    der PHP CLI beschreibbar sein.




Erweiterung durch Plugin
------------------------

Das Plugin "*jtl_plugin_bootstrapper*" erweitert die Shop CLI um den Befehl "*create-plugin*". |br|
Wenn dieses Plugin in JTL-Shop installiert ist, können mit der Shop CLI den Befehl
``jtl_plugin_bootstrapper:create-plugin`` aufrufen, um sich die grundlegende Struktur eines Plugins erzeugen
zu lassen.

Der Befehl ``jtl_plugin_bootstrapper`` kann alleinstehend aufgerufen werden, fragt aber dann interaktiv, ob der
einzige Sub-Befehl ``create-plugin`` aufgerufen werden soll. |br|
Der Sub-Befehl ``create-plugin`` fragt nun interaktiv alle erforderlichen Parameter ab und erzeugt sodann
alle erforderlichen Verzeichnisse und Dateien im Ordner ``plugins/``.

Ist ein Ausführen des Sub-Befehl ``create-plugin`` per Script gewünscht, können alle Parameter
auch in einem Shell-Script übergeben werden. |br|
Hier ein Beispiel:

.. code-block:: sh

    #!/bin/env bash

    PLUGIN_NAME="TestPlugin"                 # Name des Plugins
    PLUGIN_VERSION="1.0.0"                   # Version des Plugin (SemVer-konform)
    DESCRIPTION="Dies ist eine Test-Plugin"  # Beschreibungstext des Plugins
    AUTHOR="Max Mustermann"                  # Name des Authors
    URL="http://example.com/info.html"       # URL zur Info-Seite
    ID="test_plugin"                         # Plugin-ID (Verzeichnisname, in dem das Plugin angelegt wird)
    FLUSH_TAGS="CACHING_GROUP_PRODUCT"       # Caching-Gruppen-Konstanten, die bei Installation gelöscht werden sollen (kommagetrennte Liste)
    MINSHOPVERSION="5.0.0"                   # minimale Shop-Version, in der das Plugin noch lauffähig ist (SemVer-konform)
    MAXSHOPVERSION="5.1.3"                   # maximale Shop-Version, in der das Plugin noch lauffähig ist (SemVer-konform)
    CREATE_MIGRATIONS="tplugin_table"        # Migrations zur Tabellerstellung erzeugen (mehrere Tabellen getrennt durch Komma)
    CREATE_MODELS="Yes"                      # Model erstellen, für neue Tabellen? (Yes/No)
    HOOKS="61,62"                            # Hooks, die genutzt werden sollen (kommagetrennt und numerisch)
    JS="main.js"                             # Javascript-Dateien, die erzeugt werden sollen (kommagetrennte Liste)
    CSS="main.css"                           # CSS-Dateien, die erzeugt werden sollen (kommagetrennte Liste)
    DELETE="Yes"                             # Soll das Plugin, bei Installation, eine alte Version ersetzen? (Yes/No)
    LINKS="test-plugin"                      # Frontend-Link-Name des Plugins (SEO-konformer, kommagetrennte Liste)
    SETTINGS="Textarea Test,Checkbox Test"   # Backend-Setting-Name (kommagetrennte Liste, muß mit Settings-Typ deckungsgleich sein)
    SETTINGSTYPES="textarea,checkbox"        # Typ des Backend-Settings (kommagetrennte Liste)


    php cli jtl_plugin_bootstrapper:create     \
      --name=${PLUGIN_NAME}                    \
      --plugin-version=${PLUGIN_VERSION}       \
      --description=${DESCRIPTION}             \
      --author=${AUTHOR}                       \
      --url=${URL}                             \
      --id=${ID}                               \
      --flush-tags=${FLUSH_TAGS}               \
      --minshopversion=${MINSHOPVERSION}       \
      --maxshopversion=${MAXSHOPVERSION}       \
      --create-migrations=${CREATE_MIGRATIONS} \
      --create-models=${CREATE_MODELS}         \
      --hooks=${HOOKS}                         \
      --js=${JS}                               \
      --css=${CSS}                             \
      --delete=${DELETE}                       \
      --links=${LINKS}                         \
      --settings=${SETTINGS}                   \
      --settingstypes=${SETTINGSTYPES}         \

Nicht alle Parameter sind Pflichtangaben.
Für den Parameter SETTINGSTYPES sind die Werte von ..(interner Link zu info.xml).. gültig

Settings-Typen
 ... (steht das schon irgendwo .... unter: plugin -> info.xml -> setting -> links)
 ab shop5 zusätzlich type 'none'

