Notifications
=============

.. |br| raw:: html

   <br />

Im Backend von JTL-Shop 5 gibt es die Möglichkeit, wichtige Informationen für den Shop-Administrator an zentraler
Stelle auszugeben. |br|

.. image:: /_images/backend_notification-area.png

Dieser Mechanismus wird "Notifications" genannt und steht ausschließlich im Backend des Onlineshops zur Verfügung.

Notifications werden vorwiegend dazu eingesetzt, unvorteilhafte oder ungültige Konfigurationen oder Fehler
des Onlineshops anzuzeigen. Sie können ebenso aus Plugins heraus generiert werden.

Die Singleton-Klasse ``Notification`` hält hierfür zum Beispiel die Methode ``add()`` bereit:

.. code-block:: php

   /**
    * @param int         $type
    * @param string      $title
    * @param string|null $description
    * @param string|null $url
    */
    public function add(int $type, string $title, string $description = null, string $url = null)

Die einfachste Form um eine Statusmeldung Ihres Plugins auszugeben, ist der direkte Aufruf von ``add()``.

**Beispiel:**

.. code-block:: php

   Notification::getInstance()
       ->add(
           NotificationEntry::TYPE_WARNING,
           $this->getPlugin()->getMeta()->getName(),
           'Plugin nicht konfiguriert!',
           Shop::getAdminURL() . '/plugin.php?kPlugin=' . $this->getID()
       );


Parameter der Methode ``add()``

+------------------+---------------------------------------------------------------------+
| Parameter        | Verwendung                                                          |
+==================+=====================================================================+
| ``$type``        | Priorität der Notification (siehe: :ref:`label_notifications_type`) |
+------------------+---------------------------------------------------------------------+
| ``$title``       | Titeltext                                                           |
+------------------+---------------------------------------------------------------------+
| ``$description`` | Beschreibungstext                                                   |
+------------------+---------------------------------------------------------------------+
| ``$url``         | optionales Linkziel, |br|                                           |
|                  | wenn die Notification auf eine Backendseite weiterleiten soll       |
+------------------+---------------------------------------------------------------------+

Eine weitere Variante, Notifications zu erzeugen, ist das Generieren eines ``NotificationEntry``-Objektes.

.. code-block:: php

   /**
    * NotificationEntry constructor.
    * @param int         $type
    * @param string      $title
    * @param null|string $description
    * @param null|string $url
   */
   public function __construct($type, $title, $description = null, $url = null)

Die Parameter zum Erzeugen eines ``NotificationEntry`` gleichen denen der ``add()``-Methode. |br|
Auf diese Weise können Notifications an einer zentralen Stelle definiert und im späteren Programmverlauf, beim
Eintreten entsprechender Zustände, einfach ausgegeben werden.

**Beispiel:**

.. code-block:: php

   // definition
   //
   $entry = (new NotificationEntry(
       NotificationEntry:: TYPE_WARNING,
       $this->getPlugin()->getMeta()->getName(),
       'Plugin nicht konfiguriert',
       Shop::getAdminURL() . '/plugin.php?kPlugin=' . $this->getID()
   ))->setPluginId($this->getPluginID());

   // publication (later)
   //
   Notification::getInstance()->addNotify($entry);


.. _label_notifications_type:

NotificationEntry Typen
-----------------------

+------------------+--------+------------------------------------------------------------------------+
| Konstante        | Wert   | mögliche Verwendung                                                    |
+==================+========+========================================================================+
| ``TYPE_INFO``    | ``0``  | (Farbe: hellgrau) allgemeine Informationen                             |
+------------------+--------+------------------------------------------------------------------------+
| ``TYPE_WARNING`` | ``1``  | (Farbe: orange) Warnungen zu Einstellungen, |br|                       |
|                  |        | die den ordnungsgemäßen Betrieb des Onlineshops beeinträchtigen können |
+------------------+--------+------------------------------------------------------------------------+
| ``TYPE_DANGER``  | ``2``  | (Farbe: rot) Warnungen zu kritischen Einstellungen und Fehlern         |
+------------------+--------+------------------------------------------------------------------------+

Das Rendern aller Notifications erfolgt bei der Initialisierung des Shop-Backends. An dieser Stelle wird auch das
Dispatcher-Event ``backend.notification`` ausgelöst. Über dieses Event ist es Plugins möglich, eigene Notifications
zu erzeugen.

.. attention::

    Die Erzeugung eines NotificationEntrys sollte keine zeitkritischen Programmschritte enthalten,
    da diese das Onlineshop-Backend blockieren können.
