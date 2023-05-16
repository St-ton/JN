import {enableCollapses, enableTabs, enableTooltip, showModal} from "./gui.js";
import {Emitter, sleep} from "./utils.js";

export class Sidebar extends Emitter
{
    constructor(io)
    {
        super();

        this.io = io;
    }

    init()
    {
        $('#publishButton').on('click', () => {
            showModal(window.publishModal);
        });

        $('#navScrollRight').on('click', () => {
            window.navtabs.scrollLeft += 64;
        });

        $('#navScrollLeft').on('click', () => {
            window.navtabs.scrollLeft -= 64;
        });

        $('.portlet-button').on('dragstart', async e => {
            let button       = $(e.target);
            let portletClass = button.data('portlet-class');
            this.emit('portletDragStarted', {portlet: portletClass});
        });

        // tabs

        enableTabs();

        // collapses

        enableCollapses();

        // tooltips

        for(const elm of $('[data-tooltip]')) {
            enableTooltip(elm);
        }

        // dropdowns

        document.querySelectorAll('[data-dropdown]').forEach(btn => {
            let dropdown = document.querySelector('#' + btn.dataset.dropdown);

            let popper = Popper.createPopper(btn, dropdown, {
                modifiers: [
                    {
                        name: 'offset', options: {
                            offset: ({popper, reference}) => [popper.width / 2 - reference.width / 2, 0]
                        }
                    }
                ]
            });

            btn.addEventListener('click', e => {
                if (!dropdown.classList.contains('shown')) {
                    dropdown.classList.add('shown');
                    popper.update();
                    e.stopPropagation()
                }
            });

            window.addEventListener('click', () => {
                if (dropdown.classList.contains('shown')) {
                    dropdown.classList.remove('shown');
                }
            });
        })

        // close buttons

        document.querySelectorAll('[data-close]').forEach(closeBtn => {
            let modal = closeBtn.closest('.modal');

            closeBtn.addEventListener('click', () => {
                modal.style.opacity = 0;

                modal.addEventListener('transitionend', () => {
                    modal.classList.remove('shown');
                }, {once: true});
            });
        });

        // resizer

        window.resizer.addEventListener('mousedown', e => {
            let resizerStartWidth = window.sidebar.offsetWidth;
            let resizerStartX = e.clientX;

            window.addEventListener('mousemove', resize);
            window.addEventListener('mouseup', stopResize);
            document.body.classList.add('resizing');

            function resize(e)
            {
                e.stopPropagation();
                setSiderbarSize(resizerStartWidth + e.clientX - resizerStartX);

                if (window.navtabs.scrollWidth > window.navtabs.offsetWidth) {
                    window.navScrollRight.classList.add('shown');
                    window.navScrollLeft.classList.add('shown');
                } else {
                    window.navScrollRight.classList.remove('shown');
                    window.navScrollLeft.classList.remove('shown');
                }
            }

            function setSiderbarSize(width)
            {
                window.sidebar.style.width = width + 'px';
                let portletsPerRow = 16;

                while (
                    portletsPerRow > 1 &&
                    window.sidebar.offsetWidth < portletsPerRow * 96 + (portletsPerRow - 1) * 22 + 24 * 2
                ) {
                    portletsPerRow --;
                }

                window.portlets.style.setProperty('--portlets-per-row', portletsPerRow);
            }

            function stopResize(e)
            {
                e.stopPropagation();
                window.removeEventListener('mousemove', resize);
                window.removeEventListener('mouseup', stopResize);
                document.body.classList.remove('resizing');
            }
        });
    }
}