Profiling
=========

.. |br| raw:: html

   <br />

MySQL
-----

As is the case with :doc:`Plugin-Profiling </shop_plugins/profiling>`, the ``PROFILE_QUERIES`` switch allows for
the recording of MySQL queries and their running time, if it is set to ``true`` in the configuration file of the
online shop.

This data will also be shown in the profiler area of the back end, in the SQL tab. |br|
Depending on the configured ``DEBUG_LEVEL`` (integer value of 0-4), more or less detailed statistics on run
SQL queries are recorded via the *NiceDB* class. |br|
Here, the queries of the current page call will then be counted, the total number will be output, or the affected tables will be
named. However, during a *Debug-Level > 3*, a backtrace also takes place, which outputs the calling function and
file.

.. note::

    Please note that with *join clauses*, individual queries will appear multiple times under individual table names.

XHProf
------

The ``PROFILE_SHOP`` switch activates *XHProf*, if set to ``true``.

Therefore, *XHProf* must be already installed and configured. Additionally, the ``xhprof_html/`` and ``xhprof_lib/`` directories
must be linked and copied to the root directory of the online shop. |br|
A link to the relevant profile is then added to the end of the DOM, via a simple echo command. This is not
compliant with HTML standards. However, it does function quite nicely for this purpose in particular. A more elegant way of doing this could be done
by installing *xhgui*, which first requires the installation of a *MongoDB* server. *Xhgui* can then access this data
as well, while providing a nicer interface.

Plug-in profiling
-----------------

Even for plug-in runtime measurement, there is an internal JTL-Shop option for this. |br|

For further information, please see: doc:`"Plugin-Profiling" </shop_plugins/profiling>`.
