console.log('custom-gtag.js');
// custom-gtag.js
document.addEventListener('DOMContentLoaded', function() {
    // Check if cookies are set
    var utm_source = gi_GetCookie('utm_source');
    var utm_medium = gi_GetCookie('utm_medium');
    var utm_campaign = gi_GetCookie('utm_campaign');
    var utm_term = gi_GetCookie('utm_term');
    var utm_content = gi_GetCookie('utm_content');
    var utm_sent = gi_GetCookie('utm_sent');
    var utm_id = gi_GetCookie('utm_id');

    // If all UTM parameters are present, send them to GA
    if (utm_source && utm_medium && utm_campaign && utm_term && utm_content && !utm_sent) {
        // Send to GA with event utm_parameters
        gtag('event', 'utm_parameters', {
            'utm_source': utm_source,
            'utm_medium': utm_medium,
            'utm_campaign': utm_campaign,
            'utm_term': utm_term,
            'utm_content': utm_content,
            'utm_id': utm_id,
        });

        gi_SetCookie('utm_sent', 'true', 3650); // 3650 days = 10 years
    }
});

function gi_GetCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function gi_SetCookie(name, value, days) {
    var expireDate = new Date();
    expireDate.setTime(expireDate.getTime() + (days * 24 * 60 * 60 * 1000)); // Convert days to milliseconds
    var expires = "expires=" + expireDate.toUTCString();
    document.cookie = name + "=" + value + "; " + expires + "; path=/";
}