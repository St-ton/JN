/*!
 * FileInput German Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['de'] = {
        fileSingle: 'Datei',
        filePlural: 'Dateien',
        browseLabel: 'Auswählen &hellip;',
        removeLabel: 'Löschen',
        removeTitle: 'Ausgewählte löschen',
        cancelLabel: 'Abbrechen',
        cancelTitle: 'Hochladen abbrechen',
        uploadLabel: 'Hochladen',
        uploadTitle: 'Hochladen der ausgewählten Dateien',
        msgNo: 'Keine',
        msgNoFilesSelected: 'Keine Dateien ausgewählt',
        msgCancelled: 'Abgebrochen',
        msgZoomModalHeading: 'ausführliche Vorschau',
        msgSizeTooSmall: 'Datei "{name}" (<b>{size} KB</b>) ist zu klein und muss größer als <b>{minSize} KB</b> sein.',
        msgSizeTooLarge: 'Datei "{name}" (<b>{size} KB</b>) überschreitet maximal zulässige Upload-Größe von <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'Sie müssen mindestens <b>{n}</b> {files} zum Hochladen auswählen.',
        msgFilesTooMany: 'Anzahl der Dateien für den Upload ausgewählt <b>({n})</b> überschreitet maximal zulässige Grenze von <b>{m}</b> Stück.',
        msgFileNotFound: 'Datei "{name}" wurde nicht gefunden!',
        msgFileSecured: 'Sicherheitseinstellungen verhindern das Lesen der Datei "{name}".',
        msgFileNotReadable: 'Die Datei "{name}" ist nicht lesbar.',
        msgFilePreviewAborted: 'Dateivorschau abgebrochen für "{name}".',
        msgFilePreviewError: 'Beim Lesen der Datei "{name}" ein Fehler aufgetreten.',
        msgInvalidFileName: 'Ungültige Zeichen in Datei "{name}".',
        msgInvalidFileType: 'Ungültiger Typ für Datei "{name}". Nur Dateien der Typen "{types}" werden unterstützt.',
        msgInvalidFileExtension: 'Ungültige Erweiterung für Datei "{name}". Nur Dateien mit der Endung "{extensions}" werden unterstützt.',
        msgFileTypes: {
            'image': 'image',
            'html': 'HTML',
            'text': 'text',
            'video': 'video',
            'audio': 'audio',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'object'
        },
        msgUploadAborted: 'Der Datei-Upload wurde abgebrochen',
        msgUploadThreshold: 'Verarbeite...',
        msgUploadBegin: 'Initialisiere...',
        msgUploadEnd: 'Fertig',
        msgUploadEmpty: 'Keine Upload-Daten vorhanden.',
        msgValidationError: 'Validierungsfehler',
        msgLoading: 'Lade Datei {index} von {files} hoch&hellip;',
        msgProgress: 'Datei {index} von {files} - {name} - zu {percent}% fertiggestellt.',
        msgSelected: '{n} {files} ausgewählt',
        msgFoldersNotAllowed: 'Drag & Drop funktioniert nur bei Dateien! {n} Ordner übersprungen.',
        msgImageWidthSmall: 'Breite der Bilddatei "{name}" muss mindestens {size} px betragen.',
        msgImageHeightSmall: 'Höhe der Bilddatei "{name}" muss mindestens {size} px betragen.',
        msgImageWidthLarge: 'Breite der Bilddatei "{name}" nicht überschreiten {size} px.',
        msgImageHeightLarge: 'Höhe der Bilddatei "{name}" nicht überschreiten {size} px.',
        msgImageResizeError: 'Konnte Bildabmessungen nicht ändern.',
        msgImageResizeException: 'Fehler beim Ändern der Größe des Bildes.<pre>{errors}</pre>',
        msgAjaxError: 'Uploadfehler',
        msgAjaxProgressError: '{operation} fehlgeschlagen',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Dateien hierher ziehen &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'Datei entfernen',
            uploadTitle: 'Datei hochladen',
            zoomTitle: 'Details anzeigen',
            dragTitle: 'Verschieben',
            indicatorNewTitle: 'Noch nicht hochgeladen',
            indicatorSuccessTitle: 'Hochgeladen',
            indicatorErrorTitle: 'Uploadfehler',
            indicatorLoadingTitle: 'Hochladen ...'
        },
        previewZoomButtonTitles: {
            prev: 'Vorige Datei ansehen',
            next: 'Nächste Datei ansehen',
            toggleheader: 'Header',
            fullscreen: 'Vollbild',
            borderless: 'Borderless',
            close: 'schließen'
        }
    };
})(window.jQuery);
