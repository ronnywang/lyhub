<?php
include(__DIR__ . '/init.inc.php');

MiniEngine::dispatch(function($uri){
    if ($uri == '/robots.txt') {
        return ['index', 'robots'];
    }
    if (strpos($uri, '/.well-known/webfinger') === 0) {
        return ['index', 'webfinger'];
    }
    if ($uri == '/inbox') {
        return ['index', 'inbox'];
    }
    if (strpos($uri, '/users/') === 0) {
        $terms = explode('/', trim($uri, '/'));
        if (count($terms) == 2) {
            return ['index', 'user'];
        }
        if (in_array($terms[2] ?? false, [
            'outbox',
            'inbox',
            'followers',
            'following',
        ])) {
            return ['index', $terms[2]];
        }
    }
    if ($uri == '/viewer') {
        return ['viewer', 'index'];
    }
    if (strpos($uri, '/viewer/') === 0) {
        return ['viewer', 'account'];
    }
    // default
    return null;
});
