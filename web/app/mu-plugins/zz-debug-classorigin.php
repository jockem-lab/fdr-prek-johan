<?php
add_action('plugins_loaded', function() {
    if (class_exists('FasadBridge\FasadBridge', false)) {
        $r = new ReflectionClass('FasadBridge\FasadBridge');
        error_log('FASADBRIDGE REDAN LADDAD FRAN: ' . $r->getFileName());
    } else {
        error_log('FASADBRIDGE EJ LADDAD vid plugins_loaded');
    }
}, -9999);
