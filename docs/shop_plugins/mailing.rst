Mailing
=======

Dieser Abschnitt soll einen kurzen Überblick über die Möglichkeiten zum Versenden von Emails für Plugins geben und erläutert die Unterschiede zwischen JTL-Shop3/4 und 5.0.0:

Shop 3/4
--------

Die in der info.xml definierten Mailtemplates eines Plugins können bis einschließlich Shop 4 über die Methode sendeMail() aus der *includes/mailTools.php* versendet werden.

**Achtung:** Die includes/mailTools.php muss ggf. manuell required werden.

Die Template-ID berechnet sich dabei immer aus dem Präsix **kPlugin_**, der numerischen Plugin-ID, einem weiteren **_** sowie der in der info.xml definierten ModulId.

**Achtung:** Die ModulId darf keinen Unterstrich enthalten!

Als weiteren Parameter akzeptiert die Funktion ein stdClass-Objekt, das im Smarty-Template anschließend als Variable ``$oPluginMail`` bereitgestellt wird.
Befindet sich in diesem Objekt eine Eigenschaft mit dem Namen *tkunde*, so wird versucht, die Mail an die im Kundenkonto hinterlegte Email-Adresse zu versenden.

.. code-block:: php

    $data = new stdClass();
    $data->tkunde = new Kunde(1);
    $data->test = 123;
    sendeMail('kPlugin_' . $plugin->kPlugin . '_mymailmoduleid', $data);


Shop 5
------

Das Grundprinzip in Shop 5 ist ähnlich, funktioniert nun aber über den Service ``JTL\Mail\Mailer``.
Darüber hinaus ermöglicht die neue Klasse ``JTL\Mail\Mail`` eine flexiblere Konfiguration der zu versendenden Email.

Um ein Plugin-Template analog dem o.g. Beispiel zu versenden, könnte der entsprechende Code so aussehen:

.. code-block:: php

    $data = new stdClass();
    $data->tkunde = new \JTL\Customer\Kunde(1);
    $data->test = 123;
	$mailer = JTL\Shop::Container()->get(\JTL\Mail\Mailer::class);
	$mail   = new \JTL\Mail\Mail\Mail();
	/** @var \JTL\Mail\Mailer $mailer */
	$mail = $mail->createFromTemplateID('kPlugin_' . $this->getPlugin()->getID() . '_mymailmoduleid', $data);
	$mailer->send($mail);


Alternativ lassen sich Email aber auch ohne die Vorlage versenden.

.. code-block:: php

	$mailer = JTL\Shop::Container()->get(\JTL\Mail\Mailer::class);
	$mail = new JTL\Mail\Mail\Mail();
	$mail->setToName('Test');
	$mail->setToMail('test@example.com');
	$mail->setBodyHTML('<h1>Testmail!</h1><p>Dies ist ein Test.</p>');
	$mail->setBodyText('Testmail! Dies ist ein Test....');
	$mail->setSubject('Testbetreff');
    $mail->setFromMail('info@jtl-software.com);
	$mailer->send($mail);
