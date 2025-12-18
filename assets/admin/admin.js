(function(){
  function qs(sel,root){return (root||document).querySelector(sel);}
  function qsa(sel,root){return Array.prototype.slice.call((root||document).querySelectorAll(sel));}

  function setupModuleToggles(){
    qsa('.sherman-core-module__toggle').forEach(function(btn){
      btn.addEventListener('click', function(){
        var module = btn.closest('.sherman-core-module');
        if(!module) return;
        var body = qs('.sherman-core-module__body', module);
        var expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        if(body){ body.hidden = expanded; }
      });
    });
  }

  function setupSearch(){
    var input = qs('.sherman-core-admin__search');
    if(!input) return;

    input.addEventListener('input', function(){
      var q = (input.value || '').trim().toLowerCase();
      qsa('.sherman-core-module').forEach(function(card){
        var hay = (card.getAttribute('data-search') || '');
        card.style.display = (!q || hay.indexOf(q) !== -1) ? '' : 'none';
      });
      qsa('.sherman-core-admin__category').forEach(function(cat){
        var anyVisible = qsa('.sherman-core-module', cat).some(function(card){
          return card.style.display !== 'none';
        });
        cat.style.display = anyVisible ? '' : 'none';
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    setupModuleToggles();
    setupSearch();
  });
})();