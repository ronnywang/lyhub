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
        return ['index', 'user'];
    }
    // default
    return null;
});
