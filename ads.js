(function() {
    const adUrl = 'https://supersedeaccolade.com/amkf72n2a?key=b4e01f000aa26b4231916718c7531e43';
    const cookieName = 'pop_status';
    const expireSeconds = 10;

    function setAdCookie(name, value, seconds) {
        let date = new Date();
        date.setTime(date.getTime() + (seconds * 1000));
        document.cookie = name + "=" + value + ";expires=" + date.toUTCString() + ";path=/";
    }

    function getAdCookie(name) {
        let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    function executePop() {
        if (getAdCookie(cookieName)) return;

        const win = window.open(adUrl, '_blank');

        if (win) {
            win.blur();
            window.focus();
            setAdCookie(cookieName, 'true', expireSeconds);
        }
    }

    document.addEventListener('click', function() {
        executePop();
    }, { once: true });
})();
