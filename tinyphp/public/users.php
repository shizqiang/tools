<?php


require '../autoload.php';

function get() {

}

function changePass() {
    $user = getCurrentUser();
    $oldPass = _POST_('old_pass');
    $newPass = _POST_('new_pass');
    $user->changePass($oldPass, $newPass);
}

function post() {
    
}

function cli() {
}

