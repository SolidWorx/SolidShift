import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLFormElement> {
    static targets: string[] = [ 'input' ];

    declare readonly inputTarget: HTMLInputElement;

    private value: number = 0;
    private max: number = Infinity;
    private min: number = -Infinity;

    connect (): void {
        if (this.inputTarget.value === '') {
            this.inputTarget.value = '0';
        }

        if (parseInt(this.inputTarget.value, 10) > 0) {
            this.value = parseInt(this.inputTarget.value, 10);
        }

        if (this.inputTarget.max) {
            this.max = parseInt(this.inputTarget.max, 10);
        }

        if (this.inputTarget.min) {
            this.min = parseInt(this.inputTarget.min, 10);
        }

        this.inputTarget.addEventListener('change', () => {
            this.value = parseInt(this.inputTarget.value, 10);
            this.setValue();
        });
    }

    setValue () {
        if (this.value > this.max) {
            this.value = this.max;
        }

        if (this.value < this.min) {
            this.value = this.min;
        }

        this.inputTarget.value = this.value.toString();
    }

    increment (): void {
        this.value++;
        this.setValue();
    }

    decrement (): void {
        this.value--;
        this.setValue();
    }
}
