import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = [
        'scheduleTypeSingle',
        'scheduleTypeRecurring',
        'recurringOptions',
        'startDate',
        'recurringType',
        'recurringWeekly',
        'recurringEndOccurrence',
        'recurringEndDate',
        'scheduleEndNever',
        'scheduleEndAfter',
        'scheduleEndOn',
    ];

    declare readonly recurringOptionsTarget: HTMLDivElement;
    declare readonly startDateTarget: HTMLInputElement;
    declare readonly recurringTypeTarget: HTMLInputElement;
    declare readonly recurringWeeklyTarget: HTMLInputElement;
    declare readonly scheduleTypeSingleTarget: HTMLInputElement;
    declare readonly scheduleTypeRecurringTarget: HTMLInputElement;

    declare readonly recurringEndOccurrenceTarget: HTMLDivElement;
    declare readonly recurringEndDateTarget: HTMLDivElement;
    declare readonly scheduleEndNeverTarget: HTMLInputElement;
    declare readonly scheduleEndAfterTarget: HTMLInputElement;
    declare readonly scheduleEndOnTarget: HTMLInputElement;

    connect() {
        this.recurringTypeTarget.addEventListener('change', this.checkRecurringType.bind(this));

        this.scheduleEndNeverTarget.addEventListener('change', () => {
            if (this.scheduleEndNeverTarget.checked) {
                this.recurringEndDateTarget.classList.add('d-none');
                this.recurringEndOccurrenceTarget.classList.add('d-none');
                this.recurringEndOccurrenceTarget.querySelectorAll('input').forEach((el: HTMLInputElement) => el.value = '')
            }
        });

        this.scheduleEndAfterTarget.addEventListener('change', () => {
            if (this.scheduleEndAfterTarget.checked) {
                this.recurringEndDateTarget.classList.add('d-none');
                this.recurringEndDateTarget.querySelectorAll('input').forEach((el: HTMLInputElement) => el.value = '')
                this.recurringEndOccurrenceTarget.classList.remove('d-none');
            }
        });

        this.scheduleEndOnTarget.addEventListener('change', () => {
            if (this.scheduleEndOnTarget.checked) {
                this.recurringEndDateTarget.classList.remove('d-none');
                this.recurringEndDateTarget.querySelectorAll('input').forEach((el: HTMLInputElement) => el.value = '')

                this.recurringEndOccurrenceTarget.classList.add('d-none');
                this.recurringEndOccurrenceTarget.querySelectorAll('input').forEach((el: HTMLInputElement) => el.value = '')
            }
        });

        if (this.scheduleTypeRecurringTarget.checked) {
            this.enableRecurring();
            this.checkRecurringType();
        } else if (this.scheduleTypeSingleTarget.checked) {
            this.disableRecurring();
        }

        if (this.scheduleEndNeverTarget.checked) {
            this.recurringEndDateTarget.classList.add('d-none');
            this.recurringEndOccurrenceTarget.classList.add('d-none');
        } else if (this.scheduleEndAfterTarget.checked) {
            this.recurringEndDateTarget.classList.add('d-none');
            this.recurringEndOccurrenceTarget.classList.remove('d-none');
        } else if (this.scheduleEndOnTarget.checked) {
            this.recurringEndDateTarget.classList.remove('d-none');
            this.recurringEndOccurrenceTarget.classList.add('d-none');
        }
    }

    isRecurring(e: Event) {
        switch ((e.target as HTMLInputElement).value) {
        case 'single':
            this.disableRecurring();
            break;
        case 'recurring':
            this.enableRecurring();
            break;
        }
    }

    private enableRecurring () {
        this.recurringOptionsTarget.classList.remove('d-none');
        (this.startDateTarget.querySelector('label.form-label') as HTMLLabelElement).innerText = 'Start Date';
        //this.startDateTarget.classList.add('d-none');
        //this.startDateTarget.querySelector('label')?.classList.remove('required');
        //(this.startDateTarget.querySelector('input') as HTMLInputElement).required = false;
    }

    private disableRecurring () {
        this.recurringOptionsTarget.classList.add('d-none');
        (this.startDateTarget.querySelector('label.form-label') as HTMLLabelElement).innerText = 'Shift Date';
        //this.startDateTarget.classList.remove('d-none');
        //this.startDateTarget.querySelector('label')?.classList.add('required');
        //(this.startDateTarget.querySelector('input') as HTMLInputElement).required = true;
    }

    private checkRecurringType () {

        switch (this.recurringTypeTarget.value) {
        case 'weekly':
            this.recurringWeeklyTarget.classList.remove('d-none');
            break;
        default:
            this.recurringWeeklyTarget.classList.add('d-none');
            break;
        }
    }
}
