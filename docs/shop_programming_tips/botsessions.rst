Bot-Sessions
============

.. |br| raw:: html

   <br />

Mit JTL-Shop 4.x wurde das Session-Handling in Bezug auf Web-Crawler erneuert.

So kann an Bots und Crawler stets dieselbe Session ausgeliefert werden. Dies verhindert, dass eine große Anzahl
Sessions gestartet wird, für die sich Bots und Crawler gar nicht interessieren, da sie keine Cookies akzeptieren.

Gesteuert wird das Verhalten des Onlineshops hier durch die Konstante ``SAVE_BOT_SESSION``, die am besten
in der ``config.JTL-Shop.ini.php`` konfiguriert wird. |br|
Die zulässigen Werte sind 0, 1, 2, 3 mit der folgenden Bedeutung:

+------------------------------------+----------------------------------------------------------------------+
| Konstante                          | Bedeutung                                                            |
+====================================+======================================================================+
| ``define('SAVE_BOT_SESSION', 0);`` | behandelt Bots wie normale Kunden (Standardverhalten von JTL-Shop 3) |
+------------------------------------+----------------------------------------------------------------------+
| ``define('SAVE_BOT_SESSION', 1);`` | nutzt Standard-Session-Handler, kombiniert aber alle Bot-Sessions    |
|                                    | mit der gemeinsamen Session-ID "jtl-bot"                             |
+------------------------------------+----------------------------------------------------------------------+
| ``define('SAVE_BOT_SESSION', 2);`` | kombiniert alle Bot-Sessions und speichert sie im Objekt-Cache       |
+------------------------------------+----------------------------------------------------------------------+
| ``define('SAVE_BOT_SESSION', 3);`` | kombiniert alle Bot-Sessions und speichert nichts                    |
+------------------------------------+----------------------------------------------------------------------+
