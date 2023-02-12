window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {
    /**
     * Admin Settings
     * @constructor
     */
    var AdminGeneral = function () {
        this.setupListeners();
    };
    /**
     * Initializes the event listeners
     */
    AdminGeneral.prototype.setupListeners = function () {

        // Dialog confirm
        var confirmDialogs = document.querySelectorAll('.dlm-confirm-dialog');
        if (confirmDialogs) {
            for (var i = 0; i < confirmDialogs.length; i++) {
                confirmDialogs[i].addEventListener('click', function (e) {
                    return confirm(DLM_MAIN.i18n.confirm_dialog);
                })
            }
        }
    }
    /**
     * Global.
     * @type {AdminTools}
     */
    window.DLM.AdminGeneral = AdminGeneral;
    /**
     * Init
     */
    new window.DLM.AdminGeneral()
});
