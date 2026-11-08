import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['checkbox', 'summary', 'selected', 'clear'];

    connect() {
        this.render();
    }

    render() {
        const selected = this.checkboxTargets.filter((checkbox) => checkbox.checked);
        const names = selected.map((checkbox) => checkbox.dataset.name);

        this.summaryTarget.textContent = names.length === 0
            ? 'All employees'
            : names.length <= 2 ? names.join(', ') : `${names.slice(0, 2).join(', ')} +${names.length - 2}`;

        this.selectedTarget.replaceChildren();
        selected.forEach((checkbox) => {
            const chip = document.createElement('span');
            chip.className = 'selected-chip';
            chip.textContent = checkbox.dataset.name;
            this.selectedTarget.append(chip);
        });
        this.clearTarget.disabled = selected.length === 0;
    }

    clear() {
        this.checkboxTargets.forEach((checkbox) => { checkbox.checked = false; });
        this.render();
    }
}
