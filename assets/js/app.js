var row_actions_dialog = document.getElementById('row-actions-dialog');

if (row_actions_dialog) {
    var row_actions_title = document.getElementById('row-actions-title');
    var record_label = row_actions_dialog.querySelector('[data-record-label]');
    var view_link = row_actions_dialog.querySelector('[data-action-view]');
    var edit_link = row_actions_dialog.querySelector('[data-action-edit]');
    var role_form = row_actions_dialog.querySelector('[data-role-form]');
    var role_user_id = row_actions_dialog.querySelector('[data-role-user-id]');
    var role_select = row_actions_dialog.querySelector('[name="role"]');
    var delete_form = row_actions_dialog.querySelector('[data-delete-form]');
    var delete_action = row_actions_dialog.querySelector('[data-delete-action]');
    var delete_id = row_actions_dialog.querySelector('[data-delete-id]');
    var delete_extra = row_actions_dialog.querySelector('[data-delete-extra]');

    function setActionLink(link, url) {
        link.hidden = url === '';

        if (url !== '') {
            link.href = url;
        } else {
            link.removeAttribute('href');
        }
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest) {
            return;
        }

        var trigger = event.target.closest('[data-row-actions-trigger]');

        if (trigger) {
            row_actions_title.textContent = trigger.dataset.dialogTitle || 'Row actions';
            record_label.textContent = trigger.dataset.recordLabel || '';
            record_label.hidden = record_label.textContent === '';

            setActionLink(view_link, trigger.dataset.viewUrl || '');
            setActionLink(edit_link, trigger.dataset.editUrl || '');

            role_form.hidden = !trigger.dataset.roleUrl;
            if (trigger.dataset.roleUrl) {
                role_form.action = trigger.dataset.roleUrl;
                role_user_id.value = trigger.dataset.userId;
                role_select.value = trigger.dataset.role;
            }

            delete_form.hidden = !trigger.dataset.deleteUrl;
            if (trigger.dataset.deleteUrl) {
                delete_form.action = trigger.dataset.deleteUrl;
                delete_form.dataset.confirm = trigger.dataset.deleteMessage || 'Delete this item?';
                delete_id.name = trigger.dataset.deleteField || 'id';
                delete_id.value = trigger.dataset.deleteId || '';

                if (trigger.dataset.deleteAction) {
                    delete_action.value = trigger.dataset.deleteAction;
                    delete_action.disabled = false;
                } else {
                    delete_action.disabled = true;
                    delete_action.removeAttribute('value');
                }

                if (trigger.dataset.deleteExtraField) {
                    delete_extra.name = trigger.dataset.deleteExtraField;
                    delete_extra.value = trigger.dataset.deleteExtraValue || '';
                    delete_extra.disabled = false;
                } else {
                    delete_extra.disabled = true;
                    delete_extra.removeAttribute('name');
                    delete_extra.value = '';
                }
            }

            row_actions_dialog.showModal();
        }

        if (event.target.closest('[data-dialog-close]')) {
            row_actions_dialog.close();
        }

        if (event.target === row_actions_dialog) {
            row_actions_dialog.close();
        }
    });

    delete_form.addEventListener('submit', function (event) {
        if (!window.confirm(delete_form.dataset.confirm || 'Delete this item?')) {
            event.preventDefault();
        }
    });
}
