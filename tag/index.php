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
    Config::extend(['tag' => $state['tag']]);
}

function fn_tag_url($s) {
    global $site, $url;
    $path = $url->path;
    if ($site->tag) { // → `blog/tag/tag-slug`
        $path = URL::I($path); // remove page offset from URL path
        $path = Path::D($path, 2);
    } else if ($site->is === 'page') { // → `blog/page-slug`
        $path = Path::D($path);
    }
    return $url . ($path ? '/' . $path : "") . '/' . Extend::state('tag', 'path') . '/' . Path::N($s);
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
        $v = str_replace(' ', '-', $v);
        $tags[$v] = new Tag(TAG . DS . $v . '.page');
    }
    return $tags;
}

Hook::set('*.query', 'fn_page_query');
Hook::set('*.tags', 'fn_page_tags');

Route::lot(['%*%/%i%', '%*%'], function($path = "", $step = 1) use($state) {
    global $language, $site, $url;
    $step = $step - 1;
    $chops = explode('/', $path);
    // From the tag’s state
    if (isset($site->tag->sort)) {
        $sort = $site->tag->sort;
    // Inherit page extension’s state
    } else if (isset($site->page->sort)) {
        $sort = $site->page->sort;
    // Else…
    } else {
        $sort = [1, 'path'];
    }
    // From the tag’s state
    if (isset($site->tag->chunk)) {
        $chunk = $site->tag->chunk;
    // Inherit page extension’s state
    } else if (isset($site->page->chunk)) {
        $chunk = $site->page->chunk;
    // Else…
    } else {
        $chunk = 5;
    }
    $slug = array_pop($chops); // the tag slug
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
    if (($id = From::tag($slug)) !== false) {
        $pages = $page = [];
        Config::set('page.title', new Anemon([$language->tag, $site->title], ' &#x00B7; '));
        if ($path === $state['path']) {
            $path = implode('/', $chops);
            $r = PAGE . DS . $path;
            if ($file = File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new Page($file);
            }
            if ($files = Get::pages($r, 'page', $sort, 'slug')) {
                $files = array_filter($files, function($v) use($id, $r) {
                    if (!$k = File::exist($r . DS . $v . DS . 'kind.data')) {
                        if (!$k = Page::apart($r . DS . $v . '.page', 'kind')) {
                            return false;
                        }
                    }
                    $k = ',' . str_replace(' ', "", t(is_file($k) ? file_get_contents($k) : $k, '[', ']')) . ',';
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
                $site->is = '404';
                Shield::abort();
            }
            $tag = new Tag(TAG . DS . $slug . '.page');
            $site->is = 'pages';
            $site->tag = $tag;
            foreach ((array) $state['tag'] as $k => $v) {
                $site->tag->{'_' . $k} = $v;
            }
            if ($tag->description) {
                $site->description = $tag->description;
            }
            Config::set('page.title', new Anemon([$tag->title, $language->tag, $site->title], ' &#x00B7; '));
            Lot::set([
                'page' => $tag,
                'pager' => new Elevator($files, $chunk, $step, $url . '/' . $path . '/' . $state['path'] . '/' . $slug, $elevator, $site->is),
                'pages' => $pages,
                'parent' => $page
            ]);
            Shield::attach('pages/' . $path);
        }
    }
});