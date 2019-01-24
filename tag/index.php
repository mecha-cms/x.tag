<?php namespace fn\tag;

// Require the plug manually…
\r(__DIR__ . DS . 'engine' . DS . 'plug' . DS . '%[from,get,to]%');

// Store tag state to registry…
$state = \Extend::state('tag');
if (!empty($state['tag'])) {
    \Config::alt(['tag' => $state['tag']]);
}

function q($x, $self) {
    $query = [];
    foreach ((array) $self->kind as $v) {
        if ($slug = \To::tag($v)) {
            $query[] = \strtr($slug, '-', ' ');
        }
    }
    return $query;
}

function query($query) {
    if (!isset($query)) {
        return q($query, $this);
    }
    return (array) $query;
}

function tags($tags) {
    $out = [];
    foreach ((array) $this->query as $v) {
        $v = \strtr($v, ' ', '-');
        $out[$v] = new \Tag(TAG . DS . $v . '.page');
    }
    return new \Anemon($out);
}

\Hook::set('*.query', __NAMESPACE__ . "\\query", 0);
\Hook::set('*.tags', __NAMESPACE__ . "\\tags", 0);

\Hook::set('shield.enter', function() {
    if ($page = \Lot::get('page')) {
        $kinds = (array) $page->kind;
        \sort($kinds);
        \Config::set('has.tags', $kinds);
    }
}, 0);

\Route::lot(['%*%/%i%', '%*%'], function($path = "", $step = 1) use($config, $language, $state, $url) {
    $i = $step - 1; // 0-based index…
    $chops = \explode('/', $path);
    $sort = $config->tag('sort', $config->page('sort', [1, 'path']));
    $chunk = $config->tag('chunk', $config->page('chunk', 5));
    $s = \array_pop($chops); // the tag slug
    $path = \array_pop($chops); // the tag path
    // Get tag ID from tag slug…
    if (($id = \From::tag($s)) !== false) {
        $kinds = "";
        \Config::set('trace', new \Anemon([$language->tag, $config->title], ' &#x00B7; '));
        if ($path === $state['path']) {
            $path = \implode('/', $chops);
            $r = PAGE . DS . \To::path($path);
            if ($file = \File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new \Page($file);
            }
            $pages = \Get::pages($r, 'page', $sort, 'path');
            if ($pages->count() > 0) {
                $pages = $pages->is(function($v) use(&$kinds, $id) {
                    if ($k = \File::exist(\Path::F($v) . DS . 'kind.data')) {
                        $k = \file_get_contents($k);
                    } else if (!$k = \Page::apart($v, 'kind', null, false)) {
                        return false;
                    }
                    $k = ',' . \str_replace(' ', "", \trim($k, '[]')) . ',';
                    $kinds .= $k;
                    if (\strpos($k, ',' . $id . ',') !== false) {
                        return true;
                    }
                    return false;
                });
                if ($query = \l(\HTTP::get($config->q, ""))) {
                    $query = \explode(' ', $query);
                    \Config::set('is.search', true);
                    $pages = $pages->is(function($v) use($query) {
                        $v = \str_replace('-', "", \Path::N($v));
                        foreach ($query as $q) {
                            if (\strpos($v, $q) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });
                }
            }
            if ($f = \File::exist([
                TAG . DS . $s . '.page',
                TAG . DS . $s . '.archive'
            ])) {
                $tag = new \Tag($f);
            }
            $t = '/' . $path . '/' . $state['path'] . '/' . $s;
            $kinds = \array_unique(\array_filter(\explode(',', $kinds)));
            sort($kinds);
            \Config::set([
                'is' => [
                    'error' => false,
                    'page' => $f,
                    'pages' => $pages->vomit(null, false),
                    'tags' => e($kinds)
                ],
                'has' => [
                    'page' => $f ? 1 : 0,
                    'pages' => $pages->count(),
                    'parent' => $page
                ]
            ]);
            $title = [$tag->title, $language->tag, $config->title];
            if ($query) {
                \array_unshift($title, $language->search . ': ' . \implode(' ', $query));
            }
            $pager = new \Pager($pages->vomit(), [$chunk, $i], $url . $t);
            $pager_previous = $pager->config['direction']['<'];
            $pager_next = $pager->config['direction']['>'];
            $pages = $pages->chunk($chunk, $i)->map(function($v) {
                return new \Page($v);
            });
            \Lot::set([
                'page' => $tag,
                'pager' => $pager,
                'pages' => $pages,
                'parent' => $page
            ]);
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                \Config::set('is.error', 404);
                \Config::set('has', [
                    $pager_previous => false,
                    $pager_next => false
                ]);
                return \Shield::abort('404' . $t . '/' . $step);
            }
            \Config::set('trace', new \Anemon($title, ' &#x00B7; '));
            \Config::set('has', [
                $pager_previous => !!$pager->{$pager_previous},
                $pager_next => !!$pager->{$pager_next},
            ]);
            return \Shield::attach('pages' . $t . '/' . $step);
        }
    }
});