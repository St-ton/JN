Notifications
=============

.. |br| raw:: html

   <br />

Im Backen von JTL-Shop 5 gibt es die Möglichkeit, wichtige Informationen für den Shop-Administrator an zentraler
Stelle auszugeben. |br|

.. image:: /_images/backend_notification-area.png

Dieser Mechanismus wird "Notifications" genannt und steht ausschließlich im Backend des Onlineshops zur Verfügung.

Notifications werden vorwiegend dazu eingesetzt, um Statusmeldungen, unvorteilhafte Konfigurationen oder Fehler
des Onlineshops anzuzeigen. Sie können ebenso aus Plugins heraus generiert werden.

Die Singleton-Klasse ``Notification`` hält hierfür hautpsächlich die Methode ``add()`` bereit:

.. code-block:: php

   /**
    * @param int         $type
    * @param string      $title
    * @param string|null $description
    * @param string|null $url
    */
    public function add(int $type, string $title, string $description = null, string $url = null)

Parameter der Methode ``add()``:

+------------------+---------------------------------------------------------------------+
| Paramter         | Verwendung                                                          |
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


.. _label_notifications_type:

Notification Type:
------------------

+------------------+--------+------------------------------------------------------------------------+
| Konstante        | Wert   | mögliche Verwendung                                                    |
+==================+========+========================================================================+
| ``TYPE_NONE``    | ``-1`` | (Farbe: dunkelgrau) allgemeine Informationen                           |
+------------------+--------+------------------------------------------------------------------------+
| ``TYPE_INFO``    | ``0``  | (Farbe: hellgrau) optionale Konfigurationen                            |
+------------------+--------+------------------------------------------------------------------------+
| ``TYPE_WARNING`` | ``1``  | (Farbe: orange) Warnungen zu Einstellungen, |br|                       |
|                  |        | die den ordnungsgemäßen Betrieb des Onlineshops beeinträchtigen können |
+------------------+--------+------------------------------------------------------------------------+
| ``TYPE_DANGER``  | ``2``  | (Farbe: rot)  Warnungen zu kritischen Einstellungen, Fehler            |
+------------------+--------+------------------------------------------------------------------------+

Eine einfache Status-Warnung könnte Sie beispielsweise so ausgeben:

.. code-block:: php

   Notification::getInstance()
       ->add(
           NotificationEntry::TYPE_WARNING,
           $this->getPlugin()->getMeta()->getName(),
           'Plugin nicht konfiguriert!',
           Shop::getAdminURL() . '/plugin.php?kPlugin=' . $this->getID()
       );


Weiterhin kann mit der Methode ``addNotify``

.. code-block:: php

    /**
     * @param NotificationEntry $notify
     */
    public function addNotify(NotificationEntry $notify)
    {
        $this->array[] = $notify;
    }


...
