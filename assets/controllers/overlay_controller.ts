import { Controller } from '@hotwired/stimulus';

/*
 * Lightweight modal-style overlay. Each overlay lives inside the parent
 * <form>, so its form inputs stay associated with submission — unlike a
 * Bootstrap modal which teleports its body to <body>. CSS handles the
 * positioning; we only toggle `hidden` and a body class to lock scroll.
 *
 * Markup:
 *   <div data-controller="overlay">
 *     <button data-action="overlay#open">Open</button>
 *     <div data-overlay-target="panel" hidden>
 *       <div data-action="click->overlay#close"></div> // backdrop
 *       <div>…contents…</div>
 *     </div>
 *   </div>
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLElement> {
    static targets = ['panel'];
    static values = { openOnConnect: { type: Boolean, default: false } };

    declare readonly panelTarget: HTMLElement;
    declare readonly hasPanelTarget: boolean;
    declare readonly openOnConnectValue: boolean;

    connect(): void {
        if (this.openOnConnectValue && this.hasPanelTarget) {
            this.open();
        }
    }

    disconnect(): void {
        // If this overlay was open when its DOM is replaced (e.g. by a live
        // re-render), make sure the body scroll lock is released.
        if (this.hasPanelTarget && !this.panelTarget.hidden) {
            document.body.classList.remove('overlay-open');
        }
    }

    open(event?: Event): void {
        event?.preventDefault();

        if (!this.hasPanelTarget) {
            return;
        }

        this.panelTarget.hidden = false;
        document.body.classList.add('overlay-open');
    }

    close(event?: Event): void {
        event?.preventDefault();

        if (!this.hasPanelTarget) {
            return;
        }

        this.panelTarget.hidden = true;
        document.body.classList.remove('overlay-open');
    }
}
