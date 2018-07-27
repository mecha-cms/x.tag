<?php

// Create a `tag` folder in `lot` if it is not there
$f = LOT . DS . 'tag';
if (!Folder::exist($f)) {
    Folder::set($f, 0755);
    Guardian::kick($url->current);
}

// Require the plug manually…
r(__DIR__ . DS . 'engine' . DS . 'plug', [
    'from.php',
    'get.php',
    'to.php'
], null, Lot::get(null, []));

// Store tag state to registry…
$state = Extend::state('tag');
if (!empty($state['tag'])) {
    Config::alt(['tag' => $state['tag']]);
}

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
        $v = str_replace(' ', '-', $v);
        $tags[$v] = new Tag(TAG . DS . $v . '.page');
    }
    return $tags;
}

Hook::set('*.query', 'fn_page_query');
Hook::set('*.tags', 'fn_page_tags');

Route::lot(['%*%/%i%', '%*%'], function($path = "", $step = 1) use($language, $site, $state, $url) {
    $step = $step - 1;
    $chops = explode('/', $path);
    $sort = $site->tag('sort', $site->page('sort', [1, 'path']));
    $chunk = $site->tag('chunk', $site->page('chunk', 5));
    $s = array_pop($chops); // the tag slug
    $path = array_pop($chops); // the tag path
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
                1 => Elevator::WEST,
                2 => ['rel' => 'prev']
            ],
            '1' => [
                1 => Elevator::EAST,
                2 => ['rel' => 'next']
            ]
        ]
    ];
    // Get tag ID from tag slug…
    if (($id = From::tag($s)) !== false) {
        $kinds = "";
        $pages = $page = $tag = [];
        Config::set('trace', new Anemon([$language->tag, $site->title], ' &#x00B7; '));
        if ($path === $state['path']) {
            $path = implode('/', $chops);
            $r = PAGE . DS . To::path($path);
            if ($file = File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new Page($file);
            }
            if ($files = Get::pages($r, 'page', $sort, 'path')) {
                $files = array_filter($files, function($v) use(&$kinds, $id) {
                    if ($k = File::exist(Path::F($v) . DS . 'kind.data')) {
                        $k = file_get_contents($k);
                    } else if (!$k = Page::apart($v, 'kind')) {
                        return false;
                    }
                    $k = ',' . str_replace(' ', "", trim($k, '[]')) . ',';
                    $kinds .= $k;
                    if (strpos($k, ',' . $id . ',') !== false) {
                        return true;
                    }
                    return false;
                });
                if ($query = l(HTTP::get($site->q, ""))) {
                    $query = explode(' ', $query);
                    Config::set('is.search', true);
                    $files = array_filter($files, function($v) use($query) {
                        $v = Path::N($v);
                        foreach ($query as $q) {
                            if (strpos($v, $q) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                }
                $files = array_values($files);
                foreach (Anemon::eat($files)->chunk($chunk, $step) as $v) {
                    $pages[] = new Page($v);
                }
            }
            if ($f = File::exist([
                TAG . DS . $s . '.page',
                TAG . DS . $s . '.archive'
            ])) {
                $page->tag = ($tag = new Tag($f));
            }
            $t = '/' . $path . '/' . $state['path'] . '/' . $s;
            $kinds = array_unique(array_filter(explode(',', $kinds)));
            sort($kinds);
            Config::set([
                'is' => [
                    'error' => false,
                    'page' => false,
                    'pages' => $files,
                    'tag' => $id,
                    'tags' => e($kinds)
                ],
                'has' => [
                    'page' => 0,
                    'pages' => count($files),
                    'parent' => $f,
                    'tag' => $f ? 1 : 0,
                    'tags' => count($kinds)
                ]
            ]);
            if (empty($pages)) {
                // Greater than the maximum step or less than `1`, abort!
                Config::set('is.error', 404);
                Config::set('has', [
                    $elevator['direction']['1'] => false,
                    $elevator['direction']['-1'] => false
                ]);
                Shield::abort('404' . $t);
            }
            if ($tag->description) {
                Config::set('page.description', $tag->description);
            }
            $title = [$tag->title, $language->tag, $site->title];
            if ($query) {
                array_unshift($title, $language->search . ': ' . implode(' ', $query));
            }
            Lot::set([
                'page' => $page,
                'pager' => ($pager = new Elevator($files, [$chunk, $step], $url . $t, $elevator)),
                'pages' => $pages,
                'parent' => $tag
            ]);
            Config::set('trace', new Anemon($title, ' &#x00B7; '));
            Config::set('has', [
                $elevator['direction']['1'] => !!$pager->{$elevator['direction']['1']},
                $elevator['direction']['-1'] => !!$pager->{$elevator['direction']['-1']},
            ]);
            Shield::attach('pages' . $t);
        }
    }
});