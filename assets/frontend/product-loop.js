(function(){
  'use strict';

  function parseConfig(el){
    try{
      var raw = el.getAttribute('data-sherman-loop');
      if(!raw) return null;
      return JSON.parse(raw);
    }catch(e){
      return null;
    }
  }

  function getPagedFromUrl(){
    try{
      var url = new URL(window.location.href);
      var p = parseInt(url.searchParams.get('paged') || url.searchParams.get('page') || '0', 10);
      if(p && p > 0) return p;
      // pretty permalinks: /page/2/
      var m = url.pathname.match(/\/page\/(\d+)\/?$/);
      if(m && m[1]){
        var n = parseInt(m[1],10);
        if(n && n>0) return n;
      }
    }catch(e){}
    return 1;
  }

  function buildPageUrl(cfg, page){
    var current = new URL(window.location.href);

    // preserve current query params
    if(cfg && cfg.url_sync && cfg.url_sync_pretty && cfg.base_url){
      // pretty style only for archives (safer)
      try{
        var base = new URL(cfg.base_url, current.origin);
        // keep query string from current
        base.search = current.search;
        if(page <= 1){
          return base.toString();
        }
        var path = base.pathname;
        if(!path.endsWith('/')) path += '/';
        path = path.replace(/\/page\/\d+\/?$/,'');
        if(!path.endsWith('/')) path += '/';
        path += 'page/' + page + '/';
        base.pathname = path;
        return base.toString();
      }catch(e){
        // fall back to query param
      }
    }

    // default: query parameter
    if(page <= 1){
      current.searchParams.delete('paged');
      current.searchParams.delete('page');
    } else {
      current.searchParams.set('paged', String(page));
    }
    return current.toString();
  }

  function ajaxLoad(cfg, page){
    var params = new URLSearchParams();
    params.set('action', 'sherman_product_loop_load');
    params.set('nonce', (window.ShermanProductLoop && window.ShermanProductLoop.nonce) ? window.ShermanProductLoop.nonce : '');
    params.set('page', String(page));
    params.set('template_id', String(cfg.template_id || 0));
    params.set('settings', JSON.stringify(cfg.settings || {}));

    return fetch((window.ShermanProductLoop && window.ShermanProductLoop.ajax_url) ? window.ShermanProductLoop.ajax_url : '/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: params.toString(),
      credentials: 'same-origin'
    }).then(function(r){ return r.json(); });
  }

  function initOne(wrapper){
    var cfg = parseConfig(wrapper);
    if(!cfg) return;

    var mode = cfg.mode || 'none';
    if(mode === 'none' || mode === 'numbers') return;

    var grid = wrapper.querySelector('.sherman-product-loop-grid');
    if(!grid) return;

    var controls = wrapper.querySelector('.sherman-product-loop-controls');
    var btn = wrapper.querySelector('.sherman-product-loop-load-more');
    var status = wrapper.querySelector('.sherman-product-loop-status');
    var sentinel = wrapper.querySelector('.sherman-product-loop-sentinel');

    var lock = false;
    var currentPage = cfg.current_page || 1;
    var maxPages = cfg.max_pages || 1;
    var hasMore = cfg.has_more !== undefined ? !!cfg.has_more : (currentPage < maxPages);

    function setStatus(text){
      if(status){ status.textContent = text || ''; }
    }

    function updateControls(){
      if(btn){
        btn.disabled = lock || !hasMore;
        btn.style.display = hasMore ? '' : 'none';
      }
      if(!hasMore){
        setStatus(cfg.no_more_text || '');
      }
    }

    function syncUrl(page){
      if(!cfg.url_sync) return;
      var url = buildPageUrl(cfg, page);
      try{
        history.pushState({ shermanLoopPage: page }, '', url);
      }catch(e){}
    }

    function appendHTML(html){
      if(!html) return;
      var temp = document.createElement('div');
      temp.innerHTML = html;
      while(temp.firstChild){
        grid.appendChild(temp.firstChild);
      }
    }

    function loadPage(page, options){
      options = options || {};
      if(lock) return Promise.resolve(false);
      if(page <= currentPage) return Promise.resolve(false);
      if(page > maxPages) return Promise.resolve(false);
      lock = true;
      updateControls();
      setStatus(cfg.loading_text || 'Loading...');

      return ajaxLoad(cfg, page).then(function(res){
        if(!res || !res.success || !res.data){
          throw new Error('Bad response');
        }
        appendHTML(res.data.html || '');
        currentPage = res.data.current_page || page;
        maxPages = res.data.max_pages || maxPages;
        hasMore = !!res.data.has_more;

        if(!options.silentUrl){
          syncUrl(currentPage);
        }

        setStatus('');
        return true;
      }).catch(function(){
        setStatus(cfg.error_text || 'Error loading products.');
        return false;
      }).finally(function(){
        lock = false;
        updateControls();
      });
    }

    // Preload to match URL paged (optional, for URL sync consistency)
    var initialPaged = getPagedFromUrl();
    var preload = Promise.resolve();
    if(initialPaged > 1){
      for(var p = 2; p <= initialPaged; p++){
        (function(pageNo){
          preload = preload.then(function(){
            return loadPage(pageNo, { silentUrl: true });
          });
        })(p);
      }
    }

    preload.then(function(){
      // after preload, ensure state reflects URL (do not push again)
      hasMore = currentPage < maxPages;
      updateControls();
    });

    if(btn){
      btn.addEventListener('click', function(){
        if(lock || !hasMore) return;
        loadPage(currentPage + 1);
      });
    }

    if(mode === 'infinite' && sentinel){
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
          if(!entry.isIntersecting) return;
          if(lock || !hasMore) return;
          loadPage(currentPage + 1);
        });
      }, { root: null, rootMargin: '0px 0px ' + String(cfg.scroll_threshold || 200) + 'px 0px', threshold: 0 });

      io.observe(sentinel);
    }

    // Back/Forward: reload for correctness
    window.addEventListener('popstate', function(){
      window.location.reload();
    });

    updateControls();
  }

  function initAll(){
    var nodes = document.querySelectorAll('[data-sherman-loop]');
    if(!nodes.length) return;
    nodes.forEach(initOne);
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initAll);
  }else{
    initAll();
  }
})();
