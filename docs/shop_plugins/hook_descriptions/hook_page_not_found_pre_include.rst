HOOK_PAGE_NOT_FOUND_PRE_INCLUDE (164)
=====================================

Triggerpunkt
""""""""""""

Nach dem ``urlNotFoundRedirect()`` und vor dem Setzen des 404-Headers

Parameter
"""""""""

``bool`` **&isFileNotFound**
    **&isFileNotFound** (default ``= true``)

``string`` **&$hookInfos['key']**
    **&$hookInfos['key']** (ab Version 5.0) Hook-Variablen-Schlüssel
