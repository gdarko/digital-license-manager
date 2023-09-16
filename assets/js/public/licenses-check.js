document.addEventListener("DOMContentLoaded", (event) => {


    const form = document.getElementById('dlm-licenses-check');

    /**
     * Handle the submission
     * @param e
     * @returns {boolean}
     */
    const handle = function(e) {
        e.preventDefault();
        const data = new FormData(form);
        const http = new window.DLM.Http();
        const url = dlm_licenses_check.ajax_url + '&action=dlm_licenses_check';
        data.append('init', 1)
        http.post(url, {
            data: data,
            success: function (response, responseStatus, responseHeaders) {
                console.log(response);
                if (response.success) {

                    let wrap = form.closest('.dlm-block-licenses-check');
                    let results = wrap.querySelector('.dlm-block-licenses-check-results');
                    if(results) {
                        results.innerHTML = response.data.html;
                    }

                } else {
                    alert(response.data.message)
                }
            },
            error: function (response, responseStatus, responseHeaders) {

            }
        });
        return false;
    }


    /**
     * Attach the javascript handler.
     */
    if(form) {
        form.addEventListener('submit', handle);
    }
});