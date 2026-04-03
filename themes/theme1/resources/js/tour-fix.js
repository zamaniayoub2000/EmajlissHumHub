(function () {
    const DASHBOARD_PATH = '/dashboard';
    const REDIRECT_TO = '/spaces';

    const originalAssign = window.location.assign;
    const originalReplace = window.location.replace;

    function overrideRedirect(url) {
        if (typeof url === 'string' && url.includes(DASHBOARD_PATH)) {
            console.log('[TourFix] Dashboard redirect blocked ? redirecting to spaces');
            originalAssign.call(window.location, REDIRECT_TO);
            return true;
        }
        return false;
    }

    window.location.assign = function (url) {
        if (!overrideRedirect(url)) {
            originalAssign.call(window.location, url);
        }
    };

    window.location.replace = function (url) {
        if (!overrideRedirect(url)) {
            originalReplace.call(window.location, url);
        }
    };
})();
