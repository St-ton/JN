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
            debug: true,
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
                        confModal.off('shown').on('shown.bs.modal', function () {
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
                    content: "Mit einem Klick auf das Speichern Symbol werden deine Änderungen übernommen und sind " +
                        "ab dann im Shop sichtbar."
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
            debug: true,
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
                        confModal.off('shown').on('shown.bs.modal', function () {
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
                        confModal.off('hide').on('hide.bs.modal', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Hier siehts du deine erste einfache Animation. Wenden wir uns einer etwas umfangreicheren Aufgabe zu. Lösche dazu den erstellten Button.",
                    onShown: function (tour) {
                        this.iframe.btnTrash.on('click', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Erstelle nun ein Spaltenportlet (in der Sidebar unter 'LAYOUT') und öffne die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('shown').on('shown.bs.modal', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Setze das Layout auf die volle Breite (12). Im Tab 'Styles' änderst du 'margin-bottom' auf 50, wählst eine Hintergrundfarbe und wechselst zu Animationen.",
                    onShown: function (tour) {
                        $('#configModalBody .nav-tabs a[href="#Animation"]').on('click', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Wähle 'fade-in' als Animationsstil und gibt bei 'offset' 150 ein. Nun Kannst du die Einstellungen schließen.",
                    onShown: function (tour) {
                        confModal.off('hide').on('hide.bs.modal', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Füge in das Spaltenportlet eine Überschrift ein und öffne auch hier die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('shown').on('shown.bs.modal', function () {
                            tour.next();
                        });
                    }.bind(this),
                },
                {
                    title: "Animationen",
                    content: "Im Styles-tab gibst du unter 'margin-bottom' 250 ein und speicherst die Einstellungen.",
                    onShown: function (tour) {
                        confModal.off('hide').on('hide.bs.modal', function () {
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
                             "Bedenke aber bitte, dass 'weniger oft mehr ist'. Soll heißen: geh sparsam mit Animationen um, damit deine Kunden nicht abgelenkt oder gar verschreckt werden.</p>",
                },
            ]
        });
        tour2.init();
        tour2.start(true);
    },

    startTour_ht3: function()
    {
        var tour3 = new Tour({
            name: "tTemplate",
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
                    backdrop: true,
                    title: "Templates",
                    content: "Lerne hier wie du Templates anlegst und wiederverwendest."
                }
            ]
        });
        tour3.init();
        tour3.start(true);
    },

};
