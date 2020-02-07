Profiling
=========

.. |br| raw:: html

   <br />

MySQL
-----

Analog zum :doc:`Plugin-Profiling </shop_plugins/profiling>` erlaubt der Schalter ``PROFILE_QUERIES`` wenn auf ``true``
gesetzt, in der config-Datei des Shops,  das Mitschneiden von MySQL-Abfragen sowie die Dauer deren Ausführung.

Auch diese Daten werden im Profiler-Bereich des Backends im Tab SQL dargestellt. |br|
Je nach konfiguriertem ``DEBUG_LEVEL`` (Integer-Wert von 0-4) werden mehr oder weniger detaillierte Statistiken zu
abgsetzten SQL-Abfragen über die *NiceDB*-Klasse protokolliert. |br|
Dabei werden die Abfragen des aktuellen Seitenaufrufs gezählt, die Gesamtanzahl ausgegeben oder die betroffenen
Tabellen genannt. Bei einem *Debug-Level > 3* erfolgt außerdem ein Backtrace, der die aufrufende Funktion und Datei
ausgibt.

.. note::

    Beachten Sie bitte, dass bei *Joins* einzelne Abfragen mehrfach unter den einzelnen Tabellennamen erscheinen.

XHProf
------

Der Schalter ``PROFILE_SHOP`` aktiviert *XHProf*, wenn auf ``true`` gesetzt.

Dazu muss *XHProf* installiert und konfiguriert sein, sowie die Ordner ``xhprof_html/`` und ``xhprof_lib/`` in den
Root-Ordner des Shops kopiert bzw. verlinkt werden. |br|
Ein Link zum jeweiligen Profil wird anschließend an das Ende des DOMs (via eines einfachen ECHOs) geschrieben. Das ist
zwar nicht HTML-Standard-konform, funktioniert für diesen Zweck jedoch sehr gut. Der etwas elegantere Weg wäre die
Installation von *xhgui*, was aber die Installation eines *MongoDB*-Servers erfordert. *Xhgui* kann anschließend auf
diese Daten ebenfalls zugreifen und beitet eine etwas hübschere Oberfläche.

Plugin-Profiling
----------------

Auch für die Ausführungzeitmessung von Plugins steht eine shop-interne Möglichkeit bereit. |br|

Weiter Informationen entnehmen Sie bitte dem Abschnitt :doc:`"Plugin-Profiling" </shop_plugins/profiling>`.
