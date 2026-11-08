import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['preset', 'dates'];

    connect() {
        this.render();
    }

    render() {
        this.datesTargets.forEach((element) => {
            element.hidden = this.presetTarget.value !== 'custom';
        });
    }

    useCustomPeriod() {
        this.presetTarget.value = 'custom';
        this.render();
    }
}
