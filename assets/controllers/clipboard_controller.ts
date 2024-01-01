import { Controller } from '@hotwired/stimulus';
import { Tooltip } from 'bootstrap';

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLFormElement> {
    static targets: string[] = [ 'source' ];

    declare readonly sourceTarget: HTMLElement;

    private tooltip: Tooltip | null = null;

    private TOOLTIP_TITLE: string = 'Copy to clipboard';

    connect (): void {
        this.tooltip = new Tooltip(
            this.element.querySelector('.copy-btn') as HTMLElement,
            {
                title: this.TOOLTIP_TITLE,
                placement: 'top',
                trigger: 'hover',
            },
        );
    }

    copy (e: Event): void {
        e.preventDefault();

        navigator.clipboard.writeText(this.sourceTarget.innerText).then((r: void) => {
            this.tooltip?.setContent({ '.tooltip-inner': 'Copied!' });

            setTimeout((): void => {
                this.tooltip?.setContent({ '.tooltip-inner': this.TOOLTIP_TITLE });
            }, 2000);

            return r;
        });
    }
}
