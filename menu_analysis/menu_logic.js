// Extracted from assets/styles/main.js
MX.secBuilder = function () {
  var body = $('body');

  var navBar =
  '<header>'+
  '<nav class="navbar navbar-expand-md navbar-dark bg-light navbar-inverse navbar-fixed-top">'+
  '<div class="container">'+
      '<a href="https://www.gob.mx/" target="_blank" class="navbar-brand">'+
          '<img src="https://framework-gb.cdn.gob.mx/gobmx/img/logo_blanco.svg" alt="P&aacutegina de inicio, Gobierno de M&eacutexico" class="logos" style="height: 46px;width: auto;">'+
      '</a>'+
      ' <button class="navbar-toggler navbar-toggler-button" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor03" aria-controls="navbarColor03" aria-expanded="false" aria-label="Toggle navigation">\n' +
      '        <span class="navbar-toggler-icon"></span>\n' +
      '      </button>'+
      '<div class="collapse navbar-collapse" id="navbarColor03">'+
          '<div class="navbar-nav nav-pills margen">'+
              '<a class="nav-item nav-link" href="https://www.gob.mx/tramites" target="_self" title="Ir a tr&aacutemites del gobierno">Tr&aacute;mites</a>'+
              '<a class="nav-item nav-link" href="https://www.gob.mx/gobierno" target="_self" title="Ir a gobierno">Gobierno</a>'+
              '<a class="nav-item nav-link" href="https://www.gob.mx/busqueda" target="_self" title="Haz búsquedas en gobierno"><i class="icon-search"></i></a>'+
          '</div>'+
      '</div>'+
    '</div>'+
  '</nav>'+
'</header>';

  // ... footer code ...

  body.prepend(navBar);
  // ...
};

$(function () {
  MX.secBuilder();
  // ...
});

// Extracted from assets/styles/gobmx.js
(function() {
  // ... script loading ...
  var scriptsCDNPath = 'https://framework-gb.cdn.gob.mx/gm/v3/assets/js/vendor/';
  var scriptsMain = 'https://framework-gb.cdn.gob.mx/gm/v3/assets/js/';

  // Loads jQuery, bootstrap.bundle.js, main.js dynamically
  // ...

  // 1. Busca el elemento nav en la página
    const navElement = document.querySelector('nav');

// 2. Verifica si el elemento nav existe
    if (navElement) {
        // 3. Si existe, cambia el margin del body
        document.body.style.marginTop = '70px'; // Puedes ajustar este valor a lo que necesites
    }
})();
