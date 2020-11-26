<?php
use models\User;

require '../autoload.php';

function post() {
    $row = User::find(['email =' => _POST_('email')]);
    if (!$row) {
        throw new Exception('email not found', 401);
    }
    $user = new User($row);
    $user->signin(_POST_('password'), _POST_('one_code'));
    $_SESSION['current_user'] = $user;
    print 'SUCCESS';
}

function get() {
    print 'Login';
}
