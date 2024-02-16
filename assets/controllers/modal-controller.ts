import { Controller } from '@hotwired/stimulus';
import Modal from 'bootstrap/js/dist/modal';

export default class extends Controller {
    initialize(): void {
        window.addEventListener(`modal:close:${this.element.id}`, (event: Event): void => {
            Modal.getInstance(this.element)?.hide()

            document.querySelectorAll('.modal-backdrop').forEach((element: Element) => element.remove())
        });
    }
}
