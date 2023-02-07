Shop CLI
========

.. |br| raw:: html

    <br />


Die Shop CLI ist ein Kommandozeilen-Tool, welches mit dem PHP-Kommandozeileninterpreter
verwendet wird und die Möglichkeit bietet, administrative Aufgaben ohne Shop-Backend
auszuführen. |br|
Um die Shop CLI verwenden zu können, muss PHP als Kommandozeileninterpreter verfügbar sein.
(siehe: PHP-Konfiguration des jeweiligen Servers)


Aufruf der Shop CLI
-------------------

Die Shop CLI wird Im Hauptverzeichnis (Installationsverzeichnis) von JTL-Shop aufgerufen:

.. code-block:: text

    $> php cli [befehl:sub-befehl [parameter]]

Ein Aufruf ohne Befehle, wie auch der Aufruf mit dem Befehl ``list``, gibt eine kurze
Liste aller verfügbaren Befehle aus.

.. code-block:: text

    $> php cli
    ...
    $> php cli list
    ...

Mehrere Befehle haben nur einen Sub-Befehl. |br|
Werden solche Befehle angegeben, fragt die Shop CLI interaktiv, ob der eine Sub-Befehl ausgeführt werden soll.


Hilfe zu Befehlen
-----------------

Mit ``help`` vor einem Befehl, wie auch mit den Parametern ``-h`` und ``--help`` nach
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

Der Befehl ``create`` erzeugt den Objektrumpf einer neuen Migration. |br|
Diese neue Migration enthält zwei leere Methoden (``up()``, ``down()``), die vom Entwickler zu implementieren sind.

``migrate:innodbutf8``
......................

Der Befehl ``innodbutf8`` konvertiert alle Tabellen der Datenbank auf die *Engine* "InnoDB", die bis dato noch mit
der Engine "MyISAM" laufen. |br|
Zudem werden all diese Tabellen auf den Zeichensatz ``CHARACTER SET 'utf8'`` und die Sortierungsregel
``COLLATE 'utf8_unicode_ci'`` umgestellt.

``migrate:status``
..................

Mit dem Befehl ``status`` können Sie sich eine Liste aller Migrationen und deren Ausführungsstatus ausgeben lassen.


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

Der Abgleich von JTL-Wawi und JTL-Shop erzeugt temporäre Dateien, die standardmäßig automatisch gelöscht werden. |br|
Wird dieses automatische Löschen durch die config-Konstante ``KEEP_SYNC_FILES`` unterbunden, können diese temporären
Dateien mit diesem Befehl gelöscht werden.

``cache:file:delete``
.....................

Sobald für JTL-Shop die Caching-Methode "Datein" (und "Dateien (erweitert)") eingestellt ist, werden diese Dateien
unter ``templates_c/filecache/`` gespeichert. JTL-Shop verwaltet das Verzeichnis ``filecache/`` automatisch.

Bei Bedarf kann mithilfe der Shop CLI und diesem Befehl das Verzeichnis geleert und entfernt werden.

``cache:tpl:delete``
....................

Für jedes aktivierte Template in JTL-Shop existiert ein Verzeichnis unterhalb des Ordners ``templates_c/``. Hier
werden alle durch Smarty vor-compilierten Dateien des jeweiligen Templates abgelegt. |br|

Mit diesem Sub-Befehl kann dieser Ordner bei Bedarf auch manuell geleert und entfernt werden.

``cache:clear``
...............

Dieser Sub-Befehl leert den jeweiligen Speicher der aktuell aktivierten Objekt-Cache-Methode. |br|
(wie im Backende eingestellt, siehe: *System -> Cache -> Einstellungen -> Methode:*)

``cache:warm``
..............

Dieser Sub-Befehl führt ein s.g. "cache warmup" durch, wobei bereits verschiedene Inhalte im Cache aufbereitet werden,
um sie schneller zur Verfügung stellen zu können.

Mit einem entsprechenden Parameter können Sie gesondert festlegen, welche Bereiche des Cache "aufgewärmt"
werden sollen:

.. code-block:: text

    -d, --details         Artikeldetails vorbereiten
    -l, --list            Artikellisten vorbereiten
    -k, --childproducts   Kindartikel vorbereiten
    -g, --linkgroups      Linkgruppen vorbereiten
    -c, --categories      Kategorien vorbereiten
    -m, --manufacturers   Hersteller vorbereiten

.. caution::

    Das Aufwärmen des Caches kann, abhängig von der Größe Ihres JTL-Shop, einige Zeit in Anspruch nehmen.

Mit dem folgenden Paramter kann man das Leeren des Caches, vor dem Aufwärmen, erzwingen:

.. code-block:: text

    -p, --preflush        Cache löschen vor dem Aufwärmen

Diese Parameter können beliebig kombiniert werden.


compile
.......

Der Befehl ``compile`` ist nicht alleinstehend aufrufbar. Er kann nur mit einem spezifischen Unterbefehl aufgerufen
werden.

``compile:less``
................

Alle *Themes* des EVO-Templates enthalten ``.less``-Dateien. |br|
Sollten Sie die ``.less``-Dateien in einem *Theme* an Ihre Bedürfnisse angepaßt haben, können Sie mit diesen Befehl
alle ``.less``-Dateien, aller Themes des EVO-Templates, in ``.css``-Dateien übersetzen.

Mit dem Parameter ``--theme=[Theme-Name]`` können Sie ein bestimmtes *Theme* angeben. |br|
Mit dem Parameter ``--templateDir=[Template-Name]`` bestimmen Sie ein anderes Template-Verzeichnis.

``compile:sass``
................

Alle *Themes* des NOVA-Templates enthalten ``.scss``-Dateien. |br|
Sollten Sie die ``.scss``-Dateien in einem *Theme* an Ihre Bedürfnisse angepaßt haben, können Sie mit diesen Befehl
alle ``.scss``-Dateien, aller Themes des NOVA-Templates, in ``.css``-Dateien übersetzen.

Dieser Befehl übersetzt ebenso das "*critical SCSS*", welches im Seitenkopf immer mit übertragen wird.

Mit dem Parameter ``--theme=[Theme-Name]`` können Sie ein bestimmtes *Theme* angeben. |br|
Mit dem Parameter ``--templateDir=[Template-Name]`` bestimmen Sie ein anderes Template-Verzeichnis.

generate
........

Der Befehl ``generate`` kann alleinstehend aufgerufen werden, fragt aber dann interaktiv, ob der einzige
Sub-Befehl aufgerufen werden soll.

``generate:demodata``
.....................

Mit diesem Befehl können Sie einfache Artikel und Kategorien in einem noch leeren JTL-Shop erzeugen,
um die grundlegende Funktion von JTL-Shop zu demonstrieren.


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

    Zum Speichern der neuen Objekte muss ein Ordner names ``models/`` im Hauptverzeichnis des Shops vorhanden und von
    der PHP CLI beschreibbar sein.




Erweiterung durch Plugin
------------------------

Das Plugin `jtl_plugin_bootstrapper <https://gitlab.com/jtl-software/jtl-shop/plugins/jtl_plugin_bootstrapper>`_
erweitert die Shop CLI um den Befehl "*create-plugin*". |br|
Wenn dieses Plugin in JTL-Shop installiert ist, können Sie mit der Shop CLI den Befehl
``jtl_plugin_bootstrapper:create-plugin`` aufrufen, um sich die grundlegende Struktur eines JTL-Shop Plugins erzeugen
zu lassen.

Der Befehl ``jtl_plugin_bootstrapper`` kann alleinstehend aufgerufen werden, fragt aber dann interaktiv, ob der
einzige Sub-Befehl ``create-plugin`` aufgerufen werden soll. |br|
Der Sub-Befehl ``create-plugin`` fragt dann seinerseits interaktiv alle erforderlichen Parameter ab und erzeugt sodann
die grundlegend erforderlichen Verzeichnisse und Dateien im Ordner ``plugins/``.

Ist ein Ausführen des Sub-Befehls ``create-plugin`` per Script gewünscht, können alle Parameter
auch in einem Shell-Script übergeben werden. |br|

Beispiel:

.. code-block:: sh

    #!/bin/env bash

    PLUGIN_NAME='TestPlugin'                 # Name des Plugins
    PLUGIN_VERSION='1.0.0'                   # Version des Plugin (SemVer-konform)
    DESCRIPTION='Dies ist eine Test-Plugin'  # Beschreibungstext des Plugins
    AUTHOR='Max Mustermann'                  # Name des Authors
    URL='http://example.com'                 # URL, beispielsweise zur Homepage des Authors
    ID='test_plugin'                         # Plugin-ID (Plugin-Verzeichnisname und Shop-interne ID)
    FLUSH_TAGS='CACHING_GROUP_PRODUCT'       # Caching-Gruppen, die bei Installation gelöscht werden sollen (kommagetrennte Liste)
    MINSHOPVERSION='5.0.0'                   # minimale Shop-Version, in der das Plugin noch lauffähig ist (SemVer-konform)
    MAXSHOPVERSION='5.1.3'                   # maximale Shop-Version, in der das Plugin noch lauffähig ist (SemVer-konform)
    CREATE_MIGRATIONS='tplugin_table'        # Migrations zur Tabellerstellung erzeugen (kommagetrennte Liste)
    CREATE_MODELS='Yes'                      # Model erstellen, für neue Tabellen? (Yes/No)
    HOOKS='61,62'                            # Hooks, die genutzt werden sollen (kommagetrennt und numerisch)
    JS='main.js'                             # Javascript-Dateien, die erzeugt werden sollen (kommagetrennte Liste)
    CSS='main.css'                           # CSS-Dateien, die erzeugt werden sollen (kommagetrennte Liste)
    DELETE='Yes'                             # Soll das Plugin, bei Installation, eine alte Version ersetzen? (Yes/No)
    LINKS='test-plugin'                      # Frontend-Link-Name des Plugins (SEO-konformer, kommagetrennte Liste)
    SETTINGS='Textarea Test,Checkbox Test'   # Backend-Setting-Name (kommagetrennte Liste, muss mit Settings-Typ deckungsgleich sein)
    SETTINGSTYPES='textarea,checkbox'        # Typ des Backend-Settings (kommagetrennte Liste)


    php cli jtl_plugin_bootstrapper:create-plugin  \
      --name="${PLUGIN_NAME}"                      \
      --plugin-version="${PLUGIN_VERSION}"         \
      --description="${DESCRIPTION}"               \
      --author="${AUTHOR}"                         \
      --url="${URL}"                               \
      --id="${ID}"                                 \
      --flush-tags="${FLUSH_TAGS}"                 \
      --minshopversion="${MINSHOPVERSION}"         \
      --maxshopversion="${MAXSHOPVERSION}"         \
      --create-migrations="${CREATE_MIGRATIONS}"   \
      --create-models="${CREATE_MODELS}"           \
      --hooks="${HOOKS}"                           \
      --js="${JS}"                                 \
      --css="${CSS}"                               \
      --delete="${DELETE}"                         \
      --links="${LINKS}"                           \
      --settings="${SETTINGS}"                     \
      --settingstypes="${SETTINGSTYPES}"           \

Nicht alle Parameter sind Pflichtangaben. |br|
Bei interaktiver Ausführung wird nur der grundlegende Teil abgefragt.

Für den Parameter ``SETTINGSTYPES`` sind die Werte, die im Abschnitt ``info.xml``
in der Tabellenzeile ":ref:`Attribut Typ <label_infoxml_settingtypes>`" gelistet sind, gültig. |br|
``SETTINGS`` (die Einstellungsnamen) und ``SETTINGSTYPES`` müssen zwei "deckungsgleiche" Arrays sein, bei denen
beispielsweise Wert 1 im Array ``SETTINGS`` auch dem Wert 1 im Array ``SETTINGSTYPES`` entspricht.

Der Parameter ``--flush-tags`` bezieht sich auf die Caching-Group-Konstanten, die in den Datei ``includes/defines_inc.php``
zu finden sind.

