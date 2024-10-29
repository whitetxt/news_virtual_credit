<div id="user" class="navbar-end">
    <?php
    require_once DB_PATH . "/users.php";
    $user = get_user_from_token($_COOKIE["sulv-token"]);
    if ($user !== false) {
        echo '<div> ' . $user->username . ' </div>';
    }
    if ($user->has_permission("ADMIN")) { ?>
        <a href="/voucher/admin/index.php" class="btn mx-2">
            <span class="material-symbols-rounded">
                admin_panel_settings
            </span>
            <span>Admin</span>
        </a>
    <?php }
    ?>
    <a href="/voucher/accounts/logout.php" class="btn">
        <span class="material-symbols-rounded">
            logout
        </span>
        Logout
    </a>
</div>