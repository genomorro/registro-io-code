import { Controller } from "@hotwired/stimulus";
import { Collapse } from "bootstrap";

export default class extends Controller {
    static targets = ["collapse"];

    connect() {
        console.log("Menu fix controller connected");
    }

    toggle(event) {
        event.preventDefault();
        const collapseElement = this.collapseTarget;
        
        // Use Bootstrap's Collapse API
        let bsCollapse = Collapse.getInstance(collapseElement);
        if (!bsCollapse) {
            bsCollapse = new Collapse(collapseElement, {
                toggle: false
            });
        }
        
        bsCollapse.toggle();
    }
}
