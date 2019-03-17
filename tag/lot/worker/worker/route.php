<?php namespace fn\route;

function tag(string $path = null, $step = 1) {
    // Load default page state(s)…
    $state = \Extend::state('tag');
    // Include global variable(s)…
    extract(\Lot::get(), \EXTR_SKIP);
    $i = $step - 1; // 0-based index…
    $chops = \explode('/', $path);
    $sort = $config->tag('sort') ?? $config->page('sort') ?? [1, 'path'];
    $chunk = $config->tag('chunk') ?? $config->page('chunk') ?? 5;
    $t = \array_pop($chops); // The tag slug
    $p = \array_pop($chops); // The tag path
    $has_tags = false;
    // Get tag ID from tag slug…
    if (null !== ($id = \From::tag($t))) {
        \Config::set('trace', new \Anemon([$language->tag, $config->title], ' &#x00B7; '));
        if ($p === $state['path']) {
            \Config::set('sort', $sort);
            \Config::set('chunk', $chunk);
            $path = \implode('/', $chops);
            $r = PAGE . DS . $path;
            if ($file = \File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new \Page($file);
            }
            $pages = \Get::pages($r, 'page', $sort, 'path');
            if ($pages->count() > 0) {
                $pages = $pages->is(function($v) use(&$has_tags, $id) {
                    if (\is_file($k = \Path::F($v) . DS . 'kind.data')) {
                        $k = \e(\file_get_contents($k));
                    } else if (!$k = \Page::apart(\file_get_contents($v), 'kind', true)) {
                        return false;
                    }
                    $k = ',' . \implode(',', (array) $k) . ',';
                    $has_tags = true;
                    return \strpos($k, ',' . $id . ',') !== false;
                });
                if ($query = \l(\HTTP::get($config->q) ?? "")) {
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
                TAG . DS . $t . '.page',
                TAG . DS . $t . '.archive'
            ])) {
                $tag = new \Tag($f);
            }
            \Config::set([
                'is' => [
                    'error' => false,
                    'page' => false,
                    'pages' => true,
                    'tags' => true
                ],
                'has' => [
                    'page' => true,
                    'pages' => $pages->count() > 0,
                    'parent' => true
                ]
            ]);
            $path = '/' . $path . '/' . $p . '/' . $t;
            $title = [$tag->title, $language->tag, $config->title];
            if ($query) {
                \array_unshift($title, $language->search . ': ' . \implode(' ', $query));
            }
            $pager = new \Pager\Pages($pages->vomit(), [$chunk, $i], $url . $path);
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
                    'next' => false,
                    'parent' => false,
                    'prev' => false
                ]);
                return \Shield::abort('404' . $path . '/' . $step);
            }
            \Config::set('trace', new \Anemon($title, ' &#x00B7; '));
            \Config::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ]);
            return \Shield::attach('pages' . $path . '/' . $step);
        }
    }
}

\Route::lot(['(.+)/(\d+)', '(.+)'], __NAMESPACE__ . "\\tag", 20);