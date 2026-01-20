/*! ** GOB.mx - Grafica Base v3.0.2 */
//  ** ultima modificacion: '14-Agosto-2025';
'use strict';

function _addEvent(e, evt, handler) {
    if (typeof handler !== 'function') return;

    // Mapear "ready" al evento real del navegador
    if (evt === "ready") {
        evt = "DOMContentLoaded";
    }

    if (e.addEventListener) {
        e.addEventListener(evt, handler, false);
    } else if (e.attachEvent) {
        e.attachEvent("on" + evt, handler);
    } else {
        e["on" + evt] = handler;
    }
}

var _events = ["ready"];

var _myLib = function(item) {
    function eventWorker(item, event) {
        this.add = function(handler) {
            _addEvent(item, event, handler);
        };
    }

    for (var i = 0; i < _events.length; i++) {
        this[_events[i]] = (new eventWorker(item, _events[i])).add;
    }
};
var $gmx = function(item) {
    return new _myLib(item);
};
// Custom event for ready gobmx-framework
(function() {
  var root ='./';
  var path =  root + 'assets/';
  var imagesPath = path + 'images/';
  var scriptsPath = path + 'js/';
  var stylesPath = path + 'styles/';
  // ruta para los js del cdn
    //  var scriptsCDNPath = '../assets/js/vendor/';
var scriptsCDNPath = 'https://framework-gb.cdn.gob.mx/gm/v3/assets/js/vendor/';

 var scriptsMain = 'https://framework-gb.cdn.gob.mx/gm/v3/assets/js/';
    // var scriptsMain = '../assets/js/';

  // se revisa que no exista Modernizr primero para cargarlo al DOM, para después mandar a llamar la funcion de carga de js.
  if(!window.Modernizr) {
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = scriptsCDNPath + 'modernizr.js';
    document.getElementsByTagName('head')[0].appendChild(script);
  };

  // Carga de pace
  var pace = document.createElement('script');
  pace.type = 'text/javascript';
  pace.src = scriptsCDNPath + 'pace.min.js';
  document.getElementsByTagName('head')[0].appendChild(pace);

  if (!window.jQuery) {
    // Cargar de jQuery
    var jq = document.createElement('script');
    jq.type = 'text/javascript';
    jq.src = scriptsMain + 'jquery.js';
    document.getElementsByTagName('head')[0].appendChild(jq);

  }
  // Espera a que jquery sea cargadp
  setTimeout(function() {
    var jq = document.createElement('script');
    jq.type = 'text/javascript';
    jq.src = scriptsMain + 'jquery-ui-datepicker.js';
    document.getElementsByTagName('head')[0].appendChild(jq);
    // Carga de bootstrap
    //carga archivo con js necesarios para bootstrap , incluyendo bootstrap.min.js
    var allScripts = document.createElement('script');
    allScripts.type = 'text/javascript';
    allScripts.src = scriptsCDNPath + 'bootstrap.bundle.js';
    document.getElementsByTagName('body')[0].appendChild(allScripts);

    // Espera a que bootstrap sea cargado
    setTimeout(function () {
      // Carga de main
      var main = document.createElement('script');
      main.type = 'text/javascript';
      main.src = scriptsMain + 'main.js';
      document.getElementsByTagName('body')[0].appendChild(main);

      var floating = document.createElement('script');
      floating.type = 'text/javascript';
      floating.src =  'https://cdn.jsdelivr.net/npm/@floating-ui/core@1.6.9';
      document.getElementsByTagName('body')[0].appendChild(floating);

      var floating_dom = document.createElement('script');
      floating_dom.type = 'text/javascript';
      floating_dom.src =  'https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.13';
      document.getElementsByTagName('body')[0].appendChild(floating_dom);
    }, 400)
  }, 400)
// 1. Busca el elemento nav en la página
    const navElement = document.querySelector('nav');

// 2. Verifica si el elemento nav existe
    if (navElement) {
        // 3. Si existe, cambia el margin del body
        document.body.style.marginTop = '70px'; // Puedes ajustar este valor a lo que necesites
    } else {
        // Opcional: Si no existe, puedes hacer algo más
        console.log('No se encontró ningún elemento <nav>.');
    }
})();
