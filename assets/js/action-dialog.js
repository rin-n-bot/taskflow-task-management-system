document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-action-dialog]');

    if (trigger) {
        var dialog_id = trigger.getAttribute('data-action-dialog');
        var action_dialog = document.getElementById(dialog_id);

        if (action_dialog && typeof action_dialog.showModal === 'function') {
            action_dialog.showModal();
        }

        return;
    }

    if (event.target.tagName === 'DIALOG' && event.target.open) {
        event.target.close();
    }
});
