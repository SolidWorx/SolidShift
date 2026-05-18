import { Controller } from '@hotwired/stimulus';

/*
 * Small inline reveal: a trigger button shows a hidden panel, then hides
 * itself so the panel can take its place. Unlike `overlay`, this is a
 * regular in-flow disclosure — no modal positioning, no body scroll lock.
 *
 * Markup:
 *   <div data-controller="reveal">
 *     <button data-action="reveal#open" data-reveal-target="trigger">Open</button>
 *     <div data-reveal-target="panel" hidden>…contents…</div>
 *   </div>
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLElement> {
    static targets = ['panel', 'trigger'];

    declare readonly panelTarget: HTMLElement;
    declare readonly hasPanelTarget: boolean;
    declare readonly triggerTarget: HTMLElement;
    declare readonly hasTriggerTarget: boolean;

    open(event?: Event): void {
        event?.preventDefault();

        if (this.hasPanelTarget) {
            this.panelTarget.hidden = false;
        }

        if (this.hasTriggerTarget) {
            this.triggerTarget.hidden = true;
        }
    }

    close(event?: Event): void {
        event?.preventDefault();

        if (this.hasPanelTarget) {
            this.panelTarget.hidden = true;
        }

        if (this.hasTriggerTarget) {
            this.triggerTarget.hidden = false;
        }
    }
}
