<?php

// Require the plug manually…
r(__DIR__ . DS . 'plug', [
    'from.php',
    'get.php',
    'to.php'
], null, Lot::get(null, []));

function fn_tag_url($s) {
    global $site, $url;
    $path = $url->path;
    if ($site->tag) { // → `blog/tag/tag-slug`
        $path = URL::I($path); // remove page offset from URL path
        $path = Path::D($path, 2);
    } else if ($site->type === 'page') { // → `blog/page-slug`
        $path = Path::D($path);
    }
    return $url . ($path ? '/' . $path : "") . '/' . Extend::state(__DIR__, 'path', 'tag') . '/' . Path::N($s);
}

Hook::set('tag.url', 'fn_tag_url');

function fn_page_query_set($content, $lot) {
    $query = [];
    $f = Path::F($lot['path']);
    if (!array_key_exists('kind', $lot)) {
        $lot['kind'] = e(File::open($f . DS . 'kind.data')->read([]));
    }
    foreach ($lot['kind'] as $v) {
        if ($slug = To::tag($v)) {
            $query[] = str_replace('-', ' ', $slug);
        }
    }
    return $query;
}

function fn_page_query($content, $lot) {
    if (!$content || !array_key_exists('query', $lot) || !file_exists(Path::F($lot['path']) . DS . 'query.data')) {
        return fn_page_query_set($content, $lot);
    }
    return $content;
}

function fn_page_tags($content, $lot) {
    global $url;
    $tags = [];
    foreach (fn_page_query_set($content, $lot) as $v) {
        $tags[] = new Page(TAG . DS . str_replace(' ', '-', $v) . '.page', [], 'tag');
    }
    return $tags;
}

Hook::set('page.query', 'fn_page_query');
Hook::set('page.tags', 'fn_page_tags');

function fn_route_tag($path = "", $step = 1) {
    global $language, $site, $url;
    $step = $step - 1;
    $chops = explode('/', $path);
    $state = Extend::state(Path::D(__DIR__));
    $sort = $state['sort'];
    $chunk = $state['chunk'];
    $ss = array_pop($chops); // the tag slug
    $sss = array_pop($chops); // the tag path
    // Based on `lot\extend\page\index.php`
    $elevator = [
        'direction' => [
           '-1' => 'previous',
            '1' => 'next'
        ],
        'union' => [
           '-2' => [
                2 => ['rel' => null]
            ],
           '-1' => [
                1 => '&#x25C0;',
                2 => ['rel' => 'prev']
            ],
            '1' => [
                1 => '&#x25B6;',
                2 => ['rel' => 'next']
            ]
        ]
    ];
    // Get tag ID from tag slug…
    if (($id = From::tag($ss)) !== false) {
        if ($sss === $state['path']) {
            $pages = [];
            $path = implode('/', $chops);
            $r = PAGE . DS . $path;
            if ($files = Get::pages($r, 'page', $sort, 'slug')) {
                $files = array_filter($files, function($v) use($id, $r) {
                    if (!$k = File::exist($r . DS . $v . DS . 'kind.data')) {
                        return false;
                    }
                    $k = ',' . str_replace(' ', "", t(file_get_contents($k), '[', ']')) . ',';
                    if (strpos($k, ',' . $id . ',') !== false) {
                        return true;
                    }
                    return false;
                });
                foreach (Anemon::eat($files)->chunk($chunk, $step) as $v) {
                    $pages[] = new Page($r . DS . $v . '.page');
                }
            }
            if (empty($pages)) {
                Shield::abort();
            }
            $page = new Page(TAG . DS . $ss . '.page', [], 'tag');
            Config::set('page.title', new Anemon([$site->title, $language->tag, $page->title], ' &#x00B7; '));
            Config::set('tag', $ss);
            Lot::set([
                'pages' => $pages,
                'page' => $page,
                'pager' => new Elevator($files, $chunk, $step, $url . '/' . $path . '/' . $state['path'] . '/' . $ss, $elevator, $site->type)
            ]);
            Shield::attach('pages/' . $path);
        }
    }
}

Route::hook(['%*%/%i%', '%*%'], 'fn_route_tag');