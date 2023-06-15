Profiling
=========

.. |br| raw:: html

   <br />

Wenn der Schalter ``PROFILE_PLUGINS`` in der ``config.JTL-Shop.ini.php`` auf ``true`` gesetzt wird, erfolgt bei jedem
Aufruf von ``executeHook()`` eine Messung der Ausführungszeit. |br|
Alle Aufrufe werden durch die Klasse *Profiler* gespeichert und am Ende eines Seitenaufbaus in der Datenbank
(Tabellen ``tprofiler`` für die einzelnen Seitenaufrufe bzw. ``tprofiler_runs`` für die einzelnen ausgeführten
Hook-Dateien inklusive der Laufzeiten) gespeichert.

Im Backend lassen sich über den Menüpunkt "*Plugin Profiler*" (URL ``/admin/profiler.php``) diese Daten einsehen. |br|
Sie werden pro Durchlauf als Highcharts-Donut-Grafik aufbereitet, die im inneren Ring die Laufzeiten pro Hook und im
Äußeren die Laufzeit pro aufgerufener Plugin-Datei anzeigt.

Auf diese Weise können bei Performance-Problemen schnell die Auslöser gefunden werden, falls es sich tatsächlich um
Plugin-Probleme handeln sollte.

Weitere Profiling-Möglichkeiten: :doc:`MySQL </shop_programming_tips/profiling>`, :doc:`XHProf </shop_programming_tips/profiling>`
