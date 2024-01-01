import { Controller } from '@hotwired/stimulus';
import intlTelInput, { Plugin } from 'intl-tel-input';
import 'intl-tel-input/build/js/utils';

import '../styles/components/telinput.scss';

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLFormElement> {
    static targets: string[] = [ 'input', 'output' ];

    declare readonly inputTarget: HTMLInputElement;
    declare readonly outputTarget: HTMLInputElement;

    container: Plugin | undefined;

    connect (): void {
        this.container = intlTelInput(this.inputTarget, {
            separateDialCode: true,
            nationalMode: true,
            initialCountry: 'auto',
            geoIpLookup: async (callback: (countryCode: string) => void): Promise<void> => {
                const item: string | null = localStorage.getItem('countryCode');

                if (!item) {
                    try {
                        const response: Response = await fetch('https://ipapi.co/json');
                        const ipData = await response.json();

                        callback(ipData.country_code);

                        // @TODO: Should we save this to the user's profile?
                        localStorage.setItem('countryCode', ipData.country_code);
                    } catch (e) {
                        callback('us');
                    }
                } else {
                    callback(item);
                }
            },
        });

        const handleChange = () => {
            if (this.inputTarget.value) {
                this.outputTarget.value = this.container?.getNumber() || '';
            }
        };

        this.inputTarget.addEventListener('change', handleChange);
        this.inputTarget.addEventListener('keyup', handleChange);

        const form: HTMLFormElement | null = this.inputTarget.closest('form');

        if (form) {
            form.addEventListener('submit', (event: SubmitEvent) => {
                // remove any existing error messages
                const errors: NodeListOf<Element> = form.querySelectorAll('.invalid-feedback');
                errors.forEach((error: Element) => error.remove());

                if (this.inputTarget.value && !this.container?.isValidNumber()) {
                    this.inputTarget.classList.add('is-invalid');
                    this.inputTarget.focus();

                    const error: HTMLDivElement = document.createElement('div');
                    error.classList.add('invalid-feedback');
                    error.innerText = 'Please enter a valid phone number.';
                    this.element.after(error);

                    // prevent form submission
                    event?.preventDefault();
                }
            });
        }
    }
}
