import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets = ['dialog', 'input'];
    open() { this.dialogTarget.showModal(); requestAnimationFrame(() => this.inputTarget.focus()); }
    close() { this.dialogTarget.close(); }
    closeOnBackdrop(event) { if (event.target === this.dialogTarget) this.close(); }
}
