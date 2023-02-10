Emails
======

.. |br| raw:: html

   <br />

In this section, a short overview of the options for sending emails via plug-ins will be provided in
addition to a brief explanation of the differences between JTL-Shops 3,4, and 5.x.

You
can read more about how to define a new email template in the ``info.xml`` of your plug-in here:":ref:`label_infoxml_email`".

JTL-Shop 3.x/4.x
----------------

Die in der ``info.xml`` definierten E-Mail-Templates eines Plugins können bis einschließlich JTL-Shop 4 über die


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

The general idea in JTL-Shop 5 is similar, however, it now functions via the ``JTL\Mail\Mailer`` service. |br|
Additionally, the new ``JTL\Mail\Mail`` class allows for more flexible configuration of the email to be sent.

In order to send a plug-in template similar to the above example, the respective code could look like this:

**Example:**

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

Alternatively, you can also send emails without a template:

.. code-block:: php

    $mailer = JTL\Shop::Container()->get(\JTL\Mail\Mailer::class);
    $mail   = new JTL\Mail\Mail\Mail();
    $mail->setToName('Test');
    $mail->setToMail('test@example.com');
    $mail->setBodyHTML(‘<h1>Test email!</h1><p>This is a test.</p>');
    $mail->setBodyText(‘Test email! This is a test....');....');
    $mail->setSubject(‘Test subject');
    $mail->setFromMail('info@jtl-software.com');
    $mail->setLanguage(JTL\Language\LanguageHelper::getDefaultLanguage());
    $mailer->send($mail);
