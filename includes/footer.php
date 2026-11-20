            </main>

            <footer class="container">
                <p>Task Flow &middot; WS101 Final Project</p>
            </footer>
        </div>
    </div>

    <dialog id="row-actions-dialog" aria-labelledby="row-actions-title">
        <div class="row">
            <h2 id="row-actions-title">Row actions</h2>
            <button type="button" class="btn" data-dialog-close aria-label="Close actions">&times;</button>
        </div>
        <p data-record-label></p>
        <div class="row" data-dialog-links>
            <a class="btn" data-action-view>View</a>
            <a class="btn" data-action-edit>Edit</a>
        </div>
        <form method="post" data-role-form hidden>
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" data-role-user-id>
            <div class="field">
                <label for="row-action-role">Role</label>
                <select id="row-action-role" name="role">
                    <option value="member">Member</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save role</button>
        </form>
        <form method="post" data-delete-form hidden>
            <input type="hidden" name="action" data-delete-action disabled>
            <input type="hidden" data-delete-id>
            <input type="hidden" data-delete-extra disabled>
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </dialog>

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js" defer></script>
</body>
</html>
