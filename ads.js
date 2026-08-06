(function() {
    const adUrl = 'https://supersedeaccolade.com/ukdruzsh45?key=927a88e2e63f0a9508d3d4fa8a470079';
    const cookieName = 'pop_status';
    const expireSeconds = 15; // Durasi diubah menjadi 15 detik

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
