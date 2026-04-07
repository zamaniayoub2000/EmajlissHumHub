humhub.module('banner', function (module, require, $) {

    module.initOnPjaxLoad = false;

    /**
     * @param isPjax
     */
    const init = function (isPjax) {
        if (isPjax) {
            return;
        }

        $(function () {
            const $root = $('#banner');
            const $content = $('#banner-content');

            function checkScrolling() {

                if ($content.width() > $root.width()) {
                    $root.addClass('scrolling');
                } else {
                    $root.removeClass('scrolling');
                }
            }

            checkScrolling();
            $(window).on('resize', checkScrolling);
        });
    };

    module.export({
        init: init
    });
});
