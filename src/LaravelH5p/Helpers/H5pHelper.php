<?php

/*
 *
 * @Project        Expression project.displayName is undefined on line 5, column 35 in Templates/Licenses/license-default.txt.
 * @Copyright      leechanrin
 * @Created        2017-04-20 오후 12:30:05 
 * @Filename       H5pHelper.php
 * @Description    
 *
 */

namespace Chali5124\LaravelH5p\Helpers;

class H5pHelper {

    //put your code here


    public static function current_user_can($permission) {
        return true;
    }
    
    public static function nonce($token) {
        return bin2hex($token);
    }

}
