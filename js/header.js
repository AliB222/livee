jQuery(document).ready(function ($) {
    // ===== تابع دریافت داده‌های هدر =====
    function fetchHeaderData() {
        $.ajax({
            url: '/livepoint/wp-content/plugins/livePoint/header-ajax.php?_=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.html) {
                    $('#liveBox').html(data.html);
                    console.log('✅ هدر به‌روز شد');
                }
            },
            error: function (xhr, status, error) {
                console.error('❌ خطا در دریافت داده‌های هدر:', error);
            }
        });
    }

    // ===== بررسی تغییرات با localStorage =====
    let lastUpdate = localStorage.getItem('lp_header_update') || '0';

    function checkForUpdates() {
        const currentUpdate = localStorage.getItem('lp_header_update') || '0';
        if (currentUpdate !== lastUpdate) {
            console.log('🔄 تغییر در پنل شناسایی شد! به‌روزرسانی هدر...');
            lastUpdate = currentUpdate;
            fetchHeaderData();
        }
    }

    // ===== اجرای اولیه =====
    fetchHeaderData();

    // ===== بررسی هر ۲ ثانیه برای تغییرات =====
    setInterval(checkForUpdates, 2000);
});