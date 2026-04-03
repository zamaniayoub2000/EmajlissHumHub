/**
 * Custom Theme - Chargeur de contenu externe via API
 *
 * BONUS : Permet de charger dynamiquement du contenu HTML
 * depuis une API externe (footer, header, ou bloc custom).
 *
 * Usage dans le champ JS du module :
 *   CustomThemeLoader.load('https://api.example.com/footer', '#custom-theme-footer');
 *   CustomThemeLoader.load('https://api.example.com/header', '#custom-theme-header');
 */
var CustomThemeLoader = (function() {
    'use strict';

    var cache = {};
    var CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

    /**
     * Charge du contenu HTML depuis une URL externe et l'injecte dans un conteneur
     * @param {string} url - URL de l'API externe
     * @param {string} selector - Sélecteur CSS du conteneur cible
     * @param {object} options - Options supplémentaires
     */
    function load(url, selector, options) {
        options = options || {};
        var target = document.querySelector(selector);
        if (!target) {
            console.warn('[CustomTheme] Conteneur non trouvé :', selector);
            return;
        }

        // Vérifier le cache
        var cacheKey = url + '|' + selector;
        if (!options.noCache && cache[cacheKey] && (Date.now() - cache[cacheKey].time < CACHE_DURATION)) {
            target.innerHTML = cache[cacheKey].html;
            return;
        }

        // Afficher un loader
        if (options.showLoader !== false) {
            target.innerHTML = '<div style="text-align:center;padding:20px;opacity:0.5;">' +
                '<i class="fa fa-spinner fa-spin"></i> Chargement...</div>';
        }

        fetch(url, {
            method: options.method || 'GET',
            headers: Object.assign({
                'Accept': 'text/html, application/json'
            }, options.headers || {}),
            credentials: options.credentials || 'omit'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            var contentType = response.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') !== -1) {
                return response.json().then(function(data) {
                    // Si JSON, chercher un champ 'html' ou 'content'
                    return data.html || data.content || data.body || JSON.stringify(data);
                });
            }
            return response.text();
        })
        .then(function(html) {
            target.innerHTML = html;
            // Mettre en cache
            cache[cacheKey] = { html: html, time: Date.now() };
            // Callback de succès
            if (typeof options.onSuccess === 'function') {
                options.onSuccess(html, target);
            }
        })
        .catch(function(error) {
            console.error('[CustomTheme] Erreur de chargement :', error);
            if (options.fallbackHtml) {
                target.innerHTML = options.fallbackHtml;
            } else {
                target.innerHTML = '';
            }
            if (typeof options.onError === 'function') {
                options.onError(error, target);
            }
        });
    }

    /**
     * Vide le cache
     */
    function clearCache() {
        cache = {};
    }

    return {
        load: load,
        clearCache: clearCache
    };
})();
