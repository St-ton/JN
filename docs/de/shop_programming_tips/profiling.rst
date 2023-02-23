Profiling
=========

.. |br| raw:: html

   <br />

MySQL
-----

Analog zum :doc:`Plugin-Profiling </shop_plugins/profiling>` erlaubt der Schalter ``PROFILE_QUERIES``, wenn er in der
config-Datei des Onlineshops auf ``true`` gesetzt ist, das Mitschneiden von MySQL-Abfragen und der Dauer deren
Ausführung.

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

Dazu muss *XHProf* installiert und konfiguriert sein. Zudem müssen die Ordner ``xhprof_html/`` und ``xhprof_lib/`` in
den Root-Ordner des Onlineshops kopiert bzw. verlinkt werden. |br|
Ein Link zum jeweiligen Profil wird anschließend an das Ende des DOMs (via eines einfachen ECHOs) geschrieben. Das ist
zwar nicht konform mit dem HTML-Standard, funktioniert für diesen Zweck jedoch sehr gut. Der etwas elegantere Weg wäre
die Installation von *xhgui*, was aber die Installation eines *MongoDB*-Servers erfordert. *Xhgui* kann anschließend
auf diese Daten ebenfalls zugreifen und bietet eine etwas hübschere Oberfläche.

Plugin-Profiling
----------------

Auch für die Ausführungszeitmessung von Plugins steht eine JTL-Shop-interne Möglichkeit bereit. |br|

Weiter Informationen entnehmen Sie bitte dem Abschnitt :doc:`"Plugin-Profiling" </shop_plugins/profiling>`.
