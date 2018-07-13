function Tutorial(gui, iframe)
{
    bindProtoOnHandlers(this);

    this.gui    = gui;
    this.iframe = iframe;
}

Tutorial.prototype = {

    constructor: Tutorial,

    init: function()
    {
        installGuiElements(this, [
            'tourModal',
            'tourForm'
        ]);
    },

    start: function()
    {
        this.tourModal.modal('show');
    },

    onTourForm: function(e)
    {
        var tourId = this.tourForm.find('input[name="help-tour"]:checked').val();

        this.tourModal.modal('hide');
        this['startTour_' + tourId]();

        e.preventDefault();
    },

    fixIframePos: function(element)
    {
        var off   = element.offset();
        var pLeft = this.gui.sidebarPanel.outerWidth();

        element.offset({left: off.left + pLeft});
    },

    fixBackdrop: function()
    {
        var backdropTop    = $('.tour-backdrop.top');
        var backdropLeft   = $('.tour-backdrop.left');
        var backdropRight  = $('.tour-backdrop.right');
        var backdropBottom = $('.tour-backdrop.bottom');

        var off       = backdropTop.offset();
        var pTop      = this.gui.topNav.height();
        var pLeft     = this.gui.sidebarPanel.outerWidth();
        var leftWidth = backdropLeft.width();

        // backdropTop.offset({top: off.top + pTop});

        off = backdropLeft.offset();
        // backdropLeft.offset({top: off.top + pTop});
        backdropLeft.width(leftWidth + pLeft);

        off = backdropRight.offset();
        // backdropRight.offset({top: off.top + pTop, left: off.left + pLeft});
        backdropRight.offset({left: off.left + pLeft});

        // off = backdropBottom.offset();
        // backdropBottom.offset({top: off.top + pTop});
    },

    startTour_ht1: function()
    {
        var confModal = this.gui.configModal;

        var tour = new Tour({
            name: "tAllgemein",
            orphan: true,
            template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div><div class='popover-navigation'>" +
                "<button class='btn btn-default' data-role='prev'>« Prev</button>" +
                "<span data-role='separator'>|</span><button class='btn btn-default' data-role='next'>Next »</button>" +
                "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                "</div></div>",
            steps: [
                {
                    backdrop: true,
                    title: "Willkommen",
                    content: "In dieser kurzen Einführung wollen wir dir einen Überblick über dieses neue Feature geben."
                },
                {
                    backdrop: true,
                    element: this.gui.sidebarPanel,
                    title: "Aufteilung",
                    content: "Grundsätzlich ist der Editor in die zwei Bereich aufgeteilt.<br>Hier siehst du die Sidebar."
                },
                {
                    backdrop: true,
                    element: this.gui.iframePanel,
                    placement: "top",
                    title: "Aufteilung",
                    content: "In diesem Bereich wird der aktuelle Stand deiner Bearbeitung gezeigt."
                },
                {
                    backdrop: true,
                    element: "#portlets",
                    placement: "right",
                    title: "Portlets",
                    content: "Das sind unserer Portlets. Diese kannst du nutzen um deine Seiten mit Inhalt zu füllen."
                },
                {
                    backdrop: true,
                    element: this.iframe.dropTargets().first(),
                    placement: "top",
                    title: "Portlets",
                    content: "Die grauen Bereiche auf dieser Seite zeigen dir wo du Portlets ablegen kannst.",
                    onShown: function (tour) {
                        this.fixIframePos($('#step-4'));
                        this.fixBackdrop();
                    }.bind(this),
                },
                {
                    element: ".portletButton:first-child",
                    placement: "bottom",
                    title: "Portlets",
                    reflex: 'dragend',
                    content: "Ziehe nun das Portlet 'Überschrift' in den obersten grauen Bereich und du hast den " +
                        "ersten Inhalt auf dieser Seite eingefügt."
                },
                {
                    element: this.iframe.portletToolbar,
                    placement: "right",
                    title: "Einstellungen",
                    onShown: function (tour) {
                        this.fixIframePos($('#step-6'));
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                    content: "An diesem Portlet siehst du eine Leiste mit verschiedenen Icons. Klicke auf den Stift" +
                        " um die Einstellungen zu öffnen."
                },
                {
                    element: "#configModal .btn-primary",
                    placement: "bottom",
                    title: "Einstellungen",
                    reflex: true,
                    content: "Alle Portlets bieten verschiedene Einstellungen. Trage hier einen neuen Text für die " +
                        "Überschrift ein und klicke auf Speichern."
                },
                {
                    element: "#btnSave",
                    placement: "bottom",
                    title: "Seite Speichern",
                    reflex: true,
                    content: "Um deine Änderungen zu Sichern klicke bitte auf das Speichern-Symbol. Beachte bitte, dass " +
                        "man im Shop noch keine Änderungen sieht."
                },
                {
                    element: "#btnPublish",
                    placement: "bottom",
                    title: "Seite Veröffentlichen",
                    reflex: true,
                    content: "Nur veröffentlichte Seiten sind für deine Kunden sichtbar. " +
                             "Um die Änderungen im Shop zu veröffentlichen klicke auf das Zeitungsymbol."
                },
                {
                    element: "#publishModal .btn-primary",
                    placement: "bottom",
                    title: "Veröffentlichen",
                    reflex: true,
                    content: "Für jede Seite kannst du verschiedene Versionen haben. " +
                             "Z.B. eine allgemeine (nur mit 'veröffentlicht ab') und eine Weihnachtsversion " +
                             "(gültig während der Weihnachtszeit). Klicke auf 'veröffentlichen ab' und speichere danach."
                },
                {
                    element: "#btnClose",
                    placement: "bottom",
                    title: "Beenden",
                    reflex: true,
                    content: "Du kannst nun den OPC beenden und deine Seite anschauen oder mit einem Klick auf 'end Tour' diese Hilfe beenden." +
                             "Das waren die Basics. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!"
                },

            ]
        });

        // Initialize the tour
        tour.init();
        $('.tour-tAllgemein-5-element').on('dragend', function () {
            tour.next();
        });
        // Initialize the tour
        tour.start(true);
    },

    startTour_ht2: function()
    {
        var confModal = this.gui.configModal;

        var tour2 = new Tour({
            name: "tAnimation",
            smartPlacement: false,
            orphan: true,
            template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div><div class='popover-navigation'>" +
                "<button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span>" +
                "<button class='btn btn-default' data-role='next'>Next »</button>" +
                "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                "</div></div>",
            steps: [
                {
                    element: '',
                    backdrop: true,
                    title: "Animationen",
                    content: "Lerne hier wie du Portlets mit einfachen Animationen erstellst. Es empfiehlt sich mit einer leeren Seite zu arbeiten."
                },
                {
                    backdrop: true,
                    title: "Animationen",
                    content: "Nicht jedes Portlet verfügt über die Einstellungen um Animationen zu erstellen."
                },
                {
                    title: "Animationen",
                    content: "Ziehe zunächst einen Button in deine Seite und öffne die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    element: "#configModalBody .nav-tabs a[href='#Animation']",
                    title: "Animationen",
                    reflex: true,
                    content: "Wechsel nun zu dem Reiter 'Animation'.",
                },
                {
                    title: "Animationen",
                    content: "Die Einstellung 'animation-style' enthält viele verschiedene Type." +
                             " Bitte wähle einen Animationsstil aus und speichere die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('hide.bs.modal.tour').on('hide.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Hier siehts du deine erste einfache Animation. Wenden wir uns einer etwas umfangreicheren Aufgabe zu. Lösche dazu den erstellten Button.",
                    onShown: function (tour) {
                        this.iframe.btnTrash.off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Erstelle nun ein Spaltenportlet (in der Sidebar unter 'LAYOUT') und öffne die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Setze das Layout auf die volle Breite (12). Im Tab 'Styles' änderst du 'margin-bottom' auf 50, wählst eine Hintergrundfarbe und wechselst zu Animationen.",
                    onShown: function (tour) {
                        $('#configModalBody .nav-tabs a[href="#Animation"]').off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Wähle 'fade-in' als Animationsstil und gibt bei 'offset' 150 ein. Nun Kannst du die Einstellungen schließen.",
                    onShown: function (tour) {
                        confModal.off('hide.bs.modal.tour').on('hide.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Füge in das Spaltenportlet eine Überschrift ein und öffne auch hier die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Im Styles-tab gibst du unter 'margin-bottom' 250 ein und speicherst die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('hide.bs.modal.tour').on('hide.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Kopiere nun das Spaltenportlet ein paar mal.",
                    onShown: function (tour) {
                        this.iframe.btnClone.off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "1x",
                    onShown: function (tour) {
                        this.iframe.btnClone.off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "2x",
                    onShown: function (tour) {
                        this.iframe.btnClone.off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "3x",
                    onShown: function (tour) {
                        this.iframe.btnClone.off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "einmal noch!",
                    onShown: function (tour) {
                        this.iframe.btnClone.off('click.tour').on('click.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "<p>Du kannst die Seite jetzt speichern und dir im Frontend anschauen. <br/>" +
                             "Beim Scrollen solltest du nun sehen, dass die verschiedenen Zeilen nacheinander eingeblendet werden. </p><p>" +
                             "Man kann dieses Konzept auch weiter ausbauen und die Bereiche z.B. abwechselnd von rechts und links in die Seite einfahren lassen. " +
                             "Mit einem schönen zweifarbigen Design lassen sich damit effektvolle Seiten kreieren. " +
                             "Bedenke aber bitte, dass 'weniger oft mehr ist'. Soll heißen: geh sparsam mit Animationen um, damit deine Kunden nicht abgelenkt oder gar verschreckt werden.</p>" +
                             "<p>Damit sind wir mit dem Tutorial 'Animationen' durch. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>",
                },
            ]
        });
        tour2.init();
        tour2.start(true);
    },

    startTour_ht3: function()
    {
        var tour3 = new Tour({
            name: "tBlueprint",
            orphan: true,
            template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                "<div class='popover-content'></div><div class='popover-navigation'>" +
                "<button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span>" +
                "<button class='btn btn-default' data-role='next'>Next »</button>" +
                "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                "</div></div>",
            steps: [
                {
                    title: "Blueprints",
                    content: "Lerne hier wie du Templates anlegst und wiederverwendest.<br/>" +
                             "Ziehe ein Spaltenportlet in die Seite und fülle die Spalten mit beliebigen Inhalt.<br/>" +
                             "Drücke dann auf 'Next' um fortzufahren."
                },
                {
                    onShown: function (tour) {
                        this.gui.blueprintModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                    title: "Blueprints",
                    content: "Selektiere das umschließende Spaltenportlet und klicke auf den Stern in der Toolbar um deine Auswahl als Vorlage zu speichern."
                },
                {
                    element: this.gui.blueprintForm,
                    backdrop: true,
                    onShown: function (tour) {
                        this.gui.blueprintForm.off('submit.tour').on('submit.tour', function () {
                            tour.next();
                        });
                    }.bind(this),
                    title: "Blueprints",
                    content: "<p>Trage hier einen aussagekräftigen Namen ein. Anhand dieses Bezeichners solltest du in Zukunft den Inhalt erkennen können.</p>" +
                             "<p>Bsp.:<br/>'Produkttabelle', 'Video und Beschreibungstext' oder '3-spaltiger Text'</p>"
                },
                {
                    element: '#composer-tabs a[href="#blueprints"]',
                    backdrop: true,
                    reflex: true,
                    title: "Blueprints",
                    content: "Alle vorhandenen Vorlagen findest du über den entsprechenden Tab in der Sidebar."
                },
                {
                    title: "Blueprints",
                    content: "<p>Du kannst jede Vorlage wie ein normales Portlet einfach in die Seite ziehen.<br/>" +
                             "Das Plugin 'JTL-Portlets' bietet bereits eine kleine Auswahl an Vorlagen, die nach der Plugininstallation hier sichtbar sind.<p/>" +
                             "<p>Damit hast du das Tutorial 'Vorlagen' abgeschlossen. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>"
                },
            ]
        });
        tour3.init();
        tour3.start(true);
    },

    startTour_ht4: function()
       {
           console.log(this.iframe);
           console.log(this.gui);

           var tour4 = new Tour({
               name: "tSettings",
               debug: true,
               orphan: true,
               template: "<div class='popover tour'><div class='arrow'></div><h3 class='popover-title'></h3>" +
                     "<div class='popover-content'></div><div class='popover-navigation'>" +
                     "<button class='btn btn-default' data-role='prev'>« Prev</button><span data-role='separator'>|</span>" +
                     "<button class='btn btn-default' data-role='next'>Next »</button>" +
                     "<button class='btn btn-primary' data-role='end' style='margin-left: 15px;'>End tour</button>" +
                     "</div></div>",
               steps: [
                   {
                       title: "Settings",
                       onShown: function (tour) {
                           this.gui.configModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       content: "Du möchtest auf unterschiedlichen Endgeräten das Design abwandeln?<br/>" +
                                "Hier erfährst du wie du über die Portleteinstellungen Einfluss auf das Aussehen nehmen kannst.<br/>" +
                                "Beginne damit ein neues Spaltenportlet in die Seite zu ziehen und die Einstellungen zu öffnen."
                   },
                   {
                       onShown: function (tour) {
                           $('#Allgemein a[href="#collapseContainerlayout-xs"]').off('click.tour').on('click.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       title: "Blueprints",
                       content: "<p>Die Einstellung 'Layout XS' definiert die Aufteilung der Spalten für z.B. Smartphones. " +
                                "Soll dieses Layout für alle Gerätegrößen verwendet werden, so sind keine weiteren Änderungen notwendig. " +
                                "Die definierte Aufteilung wird für alle Größen übernommen sofern keine weiteren Einstellungen vorgenommen werden.</p>" +
                                "<p>Schauen wir uns diese ominösen 'weiteren Einstellungen' einmal an. Klicke dazu auf die Zahnräder an der Einstellung 'Layout XS'. </p>"
                   },
                   {
                       onShown: function (tour) {
                           this.gui.configForm.off('submit.tour').on('submit.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       title: "Settings",
                       content: "<p>Hier siehst du die Spaltendefinitionen für weitere Größen. Die Einstellungen basieren auf dem Bootstrap-grid. " +
                                "Stell dir einfach vor, dass die Gesamtbreite 12 Spalten entspricht. Diese kannst du frei einteilen:</p>" +
                                "<p>Bsp.:<br/>" +
                                "'4+4+4', erzeugt drei gleichbreite Spalten<br/>" +
                                "8+4, erzeugt eine Breite und eine schmale Spalte</p>" +
                                "<p>Gib nun für das Smartphone den Wert '6+6+12+6+6' ein und speichere die Einstellungen.</p>"
                   },
                   {
                       title: "Settings",
                       onShown: function (tour) {
                           this.gui.configModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       content: "<p>Du wunderst dich vielleicht warum wir soviele Werte eingetragen haben.<br/>" +
                                "Immer wenn wir in der Summe auf oder über 12 kommen wird eine neue Zeile erstellt. Du solltest fünf Bereiche innerhalb des Portlets sehen." +
                                "Wir haben noch mehr mit diesen Spalten vor. " +
                                "Öffne dazu wieder die Einstellungen.</p>"
                   },
                   {
                       onShown: function (tour) {
                           this.gui.configForm.off('submit.tour').on('submit.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       title: "Settings",
                       content: "<p>Klappe bitte wieder die Einstellungen für alle Gerätegrößen aus.</p>" +
                                "Auf größeren Displays erscheint uns diese Aufteilung vielleicht eher ungeeignet.<br/>" +
                                "Trage in 'Layout M' bitte '3+3+6+4+8' ein.</p>" +
                                "<p>Wir verschieben also die ersten drei Spalten in eine Zeile und teilen die letzten Beiden etwas anders auf.<br/>" +
                                "Wieder gilt, dass die Einstellung für alle größeren Displays gelten (in dem Fall für 'Layout L'), auch wenn dort nichts definiert wurde.</p>" +
                                "<p>Kleiner Hinweis: Es empfiehlt sich immer die gleiche Anzahl an Spalten in jedes Einstellungsfeld einzutragen.</p>" +
                                "<p>Speichere die Einstellungen.</p>"
                   },
                   {
                       element: '#displayPreviews',
                       backdrop: true,
                       title: "Settings",
                       content: "<p>Die Spaltenaufteilung passt sich nun den Gerätegrößen an. " +
                                "Du kannst über die Vorschau die unterschiedlichen Displaybreiten simulieren um deine Einstellungen zu überprüfen.</p>" +
                                "<p>Wenn du bereit bist klicke auf 'Next'.</p>",
                   },
                   {
                       onShown: function (tour) {
                           this.gui.configModal.off('shown.bs.modal.tour').on('shown.bs.modal.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       content: "<p>Ziehe nun eine Überschrift in die Seite und öffne die Einstellungen.</p>",
                   },
                   {
                       title: "Settings",
                       onShown: function (tour) {
                           $('#configModalBody .nav-tabs a[href="#Styles"]').off('click.tour').on('click.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       content: "<p>Wechsel zu den Style-einstellungen.</p>",
                   },
                   {
                       title: "Settings",
                       onShown: function (tour) {
                           this.gui.configForm.off('submit.tour').on('submit.tour', function () {
                               tour.next();
                           });
                       }.bind(this),
                       content: "<p>Hier hast du die Möglichkeit das gesamte Portlet für bestimmte Displaygrößen auszublenden. " +
                                "Schalte die Sichtbarkeit des Portlets für Tablets aus und speichere.</p>",
                   },
                   {
                       title: "Settings",
                       content: "<p>Du kannst nun wieder über die Vorschau deine Einstellungen prüfen. </p>" +
                                "<p>Damit hast du das Tutorial 'Einstellungen' abgeschlossen. Wir wünschen dir weiterhin viel Spaß mit dem OnPage Composer!</p>"
                   },
               ]
           });
           tour4.init();
           tour4.start(true);
       },

};
