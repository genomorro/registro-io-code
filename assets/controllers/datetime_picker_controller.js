import { Controller } from "@hotwired/stimulus";
import { TempusDominus } from "@eonasdan/tempus-dominus";
import "@eonasdan/tempus-dominus/dist/css/tempus-dominus.min.css";

export default class extends Controller {
    connect() {
        console.log("Initializing Tempus Dominus on", this.element);
        new TempusDominus(this.element, {
            localization: {
                format: 'yyyy-MM-dd HH:mm',
                hourCycle: 'h23',
            },
            display: {
                inline: true,
                sideBySide: true,
                buttons: {
                    clear: false,
                    close: false
                },
                calendarWeeks: true,
                components: {
                    clock: true,
                },
            },
            useCurrent: false
        });
    }
}
