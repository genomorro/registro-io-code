import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        instanceUrl: String,
        isGuest: Boolean,
        preset: String
    }

    connect() {
        // Define the global config function before loading the script
        window.defineMetabaseConfig = (config) => {
            window.metabaseConfig = config;
        };

        window.defineMetabaseConfig({
            "theme": {
                "preset": this.hasPresetValue ? this.presetValue : "light"
            },
            "isGuest": this.hasGuestValue ? this.isGuestValue : true,
            "instanceUrl": this.hasInstanceUrlValue ? this.instanceUrlValue : "https://accesos.iner.gob.mx/metabase"
        });

        // Dynamic script injection to load Metabase embed.js
        if (!document.querySelector('script[src*="metabase/app/embed.js"]')) {
            const script = document.createElement('script');
            script.defer = true;
            script.src = "https://accesos.iner.gob.mx/metabase/app/embed.js";
            document.head.appendChild(script);
        }
    }
}
