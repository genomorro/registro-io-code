/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './stimulus_bootstrap.js';
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/menu_fix.css';

import $ from 'jquery';
import * as bootstrap from 'bootstrap';
import * as tempusDominus from '@eonasdan/tempus-dominus';

window.jQuery = window.$ = $;
window.bootstrap = bootstrap;
window.tempusDominus = tempusDominus;

// To ensure UX-Autocomplete (TomSelect) works correctly with Bootstrap 5
// and to avoid conflicts with multiple Bootstrap instances.

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
