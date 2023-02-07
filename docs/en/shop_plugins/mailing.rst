Mails
=====

.. |br| raw:: html

   <br />

Dieser Abschnitt soll einen kurzen Überblick über die Möglichkeiten zum Versenden von E-Mails über Plugins geben und
erläutert die Unterschiede zwischen JTL-Shop 3, 4 und 5.x.

Wie Sie neue E-Mail-Templates in der ``info.xml`` Ihres Plugins definieren, finden Sie
im Abschnitt ":ref:`label_infoxml_email`".

JTL-Shop 3.x/4.x
----------------

Die in der ``info.xml`` definierten E-Mail-Templates eines Plugins können bis einschließlich JTL-Shop 4 über die
Methode ``sendeMail()`` aus der ``includes/mailTools.php`` versendet werden.

.. note::

    Die ``includes/mailTools.php`` muss ggf. manuell required werden.

Die Template-ID berechnet sich dabei immer aus dem Präfix ``kPlugin_``, der numerischen *Plugin-ID,* einem
weiteren ``_`` sowie der in der ``info.xml`` definierten ``ModulId``.

**Regel:** ``kPlugin_[PluginID]_[ModulId]``

.. important::

    Die ``ModulId`` darf keinen Unterstrich enthalten!

Als weiteren Parameter akzeptiert die Funktion ein *stdClass*-Objekt, das im Smarty-Template anschließend als
Variable ``$oPluginMail`` bereitgestellt wird. Befindet sich in diesem Objekt eine Eigenschaft mit dem
Namen ``tkunde``, so wird versucht, die E-Mail an die im Kundenkonto hinterlegte E-Mail-Adresse zu versenden.

**Beispiel:**

.. code-block:: php

    $data = new stdClass();
    $data->tkunde = new Kunde(1);
    $data->test = 123;
    sendeMail('kPlugin_' . $plugin->kPlugin . '_mymailmoduleid', $data);


JTL-Shop 5.x
------------

Das Grundprinzip in JTL-Shop 5 ist ähnlich, funktioniert nun aber über den Service ``JTL\Mail\Mailer``. |br|
Darüber hinaus ermöglicht die neue Klasse ``JTL\Mail\Mail`` eine flexiblere Konfiguration der zu versendenden E-Mail.

Um ein Plugin-Template analog dem o.g. Beispiel zu versenden, könnte der entsprechende Code so aussehen:

**Beispiel:**

.. code-block:: php
   :emphasize-lines: 7

    $data = new stdClass();
    $data->tkunde = new \JTL\Customer\Kunde(1);
    $data->test = 123;
    $mailer = JTL\Shop::Container()->get(\JTL\Mail\Mailer::class);
    $mail   = new \JTL\Mail\Mail\Mail();
    /** @var \JTL\Mail\Mailer $mailer */
    $mail = $mail->createFromTemplateID('kPlugin_' . $this->getPlugin()->getID() . '_mymailmoduleid', $data);
    $mailer->send($mail);

Alternativ lassen sich E-Mails aber auch ohne Vorlage versenden:

.. code-block:: php

    $mailer = JTL\Shop::Container()->get(\JTL\Mail\Mailer::class);
    $mail   = new JTL\Mail\Mail\Mail();
    $mail->setToName('Test');
    $mail->setToMail('test@example.com');
    $mail->setBodyHTML('<h1>Testmail!</h1><p>Dies ist ein Test.</p>');
    $mail->setBodyText('Testmail! Dies ist ein Test....');
    $mail->setSubject('Testbetreff');
    $mail->setFromMail('info@jtl-software.com');
    $mail->setLanguage(JTL\Language\LanguageHelper::getDefaultLanguage());
    $mailer->send($mail);
