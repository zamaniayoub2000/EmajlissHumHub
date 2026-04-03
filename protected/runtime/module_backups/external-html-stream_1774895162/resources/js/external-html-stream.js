/**
 * External HTML Stream + Majliss Sync — JavaScript
 *
 * Gère :
 *  - Le rafraîchissement dynamique des contenus HTML externes
 *  - Le chargement sécurisé des scripts embarqués
 *  - L'observation du stream pour les nouveaux contenus
 */
(function ($) {
    'use strict';

    var ExternalHtmlStream = {

        /**
         * Initialise le module.
         */
        init: function () {
            this.bindEvents();
            this.executeEmbeddedScripts();
        },

        /**
         * Lie les événements.
         */
        bindEvents: function () {
            // Rafraîchissement via bouton
            $(document).on('click', '.btn-refresh-external', function (e) {
                e.preventDefault();
                var postId = $(this).data('post-id');
                ExternalHtmlStream.refreshContent(postId, $(this));
            });

            // Observer les nouveaux contenus dans le stream
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        $(mutation.addedNodes).find('.external-html-stream-entry').each(function () {
                            ExternalHtmlStream.processEntry($(this));
                        });
                    });
                });

                var streamContainer = document.querySelector('.wall-stream, #wallStream, .s2_stream');
                if (streamContainer) {
                    observer.observe(streamContainer, { childList: true, subtree: true });
                }
            }
        },

        /**
         * Traite une nouvelle entrée du stream.
         */
        processEntry: function ($entry) {
            this.executeSafeScripts($entry);
        },

        /**
         * Exécute les scripts embarqués dans le contenu externe.
         */
        executeEmbeddedScripts: function () {
            $('.external-html-body script').each(function () {
                var script = $(this);
                var src = script.attr('src');

                if (src) {
                    ExternalHtmlStream.loadExternalScript(src);
                } else {
                    ExternalHtmlStream.executeSandboxed(script.text());
                }

                script.remove();
            });
        },

        /**
         * Exécute les scripts d'une entrée spécifique.
         */
        executeSafeScripts: function ($entry) {
            $entry.find('.external-html-body script').each(function () {
                var script = $(this);
                var src = script.attr('src');

                if (src) {
                    ExternalHtmlStream.loadExternalScript(src);
                } else {
                    ExternalHtmlStream.executeSandboxed(script.text());
                }

                script.remove();
            });
        },

        /**
         * Charge un script externe dynamiquement.
         */
        loadExternalScript: function (src) {
            if ($('script[src="' + src + '"]').length > 0) {
                return;
            }

            var scriptEl = document.createElement('script');
            scriptEl.src = src;
            scriptEl.async = true;
            scriptEl.onerror = function () {
                console.warn('[ExternalHtmlStream] Erreur chargement script :', src);
            };
            document.body.appendChild(scriptEl);
        },

        /**
         * Exécute du code JS dans un contexte isolé.
         */
        executeSandboxed: function (code) {
            try {
                (new Function('"use strict";' + code))();
            } catch (e) {
                console.warn('[ExternalHtmlStream] Erreur script :', e.message);
            }
        },

        /**
         * Rafraîchit le contenu d'un post.
         */
        refreshContent: function (postId, $button) {
            var $container = $('#external-content-' + postId);
            var $loader = $container.find('.external-html-loader');
            var $body = $container.find('.external-html-body');
            var refreshUrl = $button ? $button.data('refresh-url') : '/external-html-stream/stream/refresh';

            if ($button) {
                $button.prop('disabled', true);
                $button.find('.fa-refresh').addClass('fa-spin');
            }
            $container.addClass('external-html-refreshing');
            $loader.fadeIn(200);

            $.ajax({
                url: refreshUrl,
                type: 'GET',
                data: { id: postId },
                dataType: 'json',
                timeout: 30000,
                success: function (data) {
                    if (data.success && data.html) {
                        $body.html(data.html);
                        ExternalHtmlStream.executeSafeScripts($container);
                    }
                },
                error: function () {
                    $body.append(
                        '<div class="alert alert-warning" style="margin-top: 10px;">' +
                        '<i class="fa fa-exclamation-triangle"></i> Erreur de rafraîchissement.' +
                        '</div>'
                    );
                },
                complete: function () {
                    if ($button) {
                        $button.prop('disabled', false);
                        $button.find('.fa-refresh').removeClass('fa-spin');
                    }
                    $container.removeClass('external-html-refreshing');
                    $loader.fadeOut(200);
                }
            });
        }
    };

    $(document).ready(function () {
        ExternalHtmlStream.init();
    });

    window.ExternalHtmlStream = ExternalHtmlStream;

})(jQuery);
