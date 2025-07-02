<?php
include(__DIR__ . '/init.inc.php');

MiniEngine::dispatch(function($uri){
    if ($uri == '/robots.txt') {
        return ['index', 'robots'];
    }
    if (strpos($uri, '/.well-known/webfinger') === 0) {
        return ['index', 'webfinger'];
    }
    if (strpos($uri, '/users/') === 0) {
        $terms = explode('/', trim($uri, '/'));
        if (count($terms) == 2) {
            return ['index', 'user'];
        }
        if (in_array($terms[2] ?? false, [
            'outbox',
        ])) {
            return ['index', $terms[2]];
        }
    }
    // default
    return null;
});
