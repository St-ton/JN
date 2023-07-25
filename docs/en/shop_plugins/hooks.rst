Hooks
=====

.. |br| raw:: html

   <br />

Hooks offer plug-ins the possibility to intervene in the execution logic of the online shop at predefined points and to
manipulate transferred data or the output document, if necessary. |br|
This is possible anywhere in the code of the online shop where the
function ``executeHook(int $nHook, array $args_arr)`` is explicitly executed.

A list of all available hooks can be found in section ":doc:`List of all hooks </shop_plugins/hook_list>`”. |br|
You can learn how to specify to the online shop which hooks your plug-in wants to use in ``info.xml``
under section ":ref:`label_infoxml_hooks`".

**Example:**

Execute a hook *before storing a news comment in the database*:

.. code-block:: php

    executeHook(\HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN, ['comment' => &$oNewsKommentar]);

Plug-ins registered on this hook can use the array ``$args_arr`` in the relevant hook file and modify it,
if necessary.

For example, if a plug-in uses the above-mentioned hook ``HOOK_NEWS_PAGE_NEWSKOMMENTAR_EINTRAGEN`` (ID 34), the comment
transferred there can be modified.

.. code-block:: php

    <?php

    if (!empty($args_arr['comment'])) {
        $args_arr['comment'] .= '<br>This text is attached to non-empty comments!';
    }

As of JTL-Shop 5.x, there is a new alternative to the familiar hooks in JTL-Shop - the *EventDispatcher*. |br|
For more information on how to make use of this new feature, see
the "Bootstrapping" section under ":ref:`label_bootstrapping_eventdispatcher`".

DOM manipulation
----------------

One of the most common application cases is to write your own HTML code into the output document. |br|
The hook ``HOOK_SMARTY_OUTPUTFILTER`` (ID 140) is used for this purpose. There, any content can be inserted into the DOM
via PHPQuery. |br|
As a simple example, a plug-in could render the contents of a template via Smarty and append it
to the body of the HTML document:

.. code-block:: php

    <?php

    $template = $oPlugin->cFrontendPfad . 'templates/' . 'example.tpl';
    pq('body')->append($smarty->fetch($template));


You can find a general overview about PHPQuery at `Documentation <https://code.google.com/archive/p/phpquery>`_.
