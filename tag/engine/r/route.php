<?php namespace _\route;

function tag() {
    $path = $this[0];
    $current = $this->url->i;
    // Load default page state(s)…
    $state = \Extend::state('tag');
    $i = $current - 1; // 0-based index…
    $chops = \explode('/', $path);
    $sort = $this->config->tag('sort') ?? $this->config->page('sort') ?? [1, 'path'];
    $chunk = $this->config->tag('chunk') ?? $this->config->page('chunk') ?? 5;
    $t = \array_pop($chops); // The tag slug
    $p = \array_pop($chops); // The tag path
    $has_tags = false;
    // Get tag ID from tag slug…
    if (null !== ($id = \From::tag($t))) {
        $this->title([$this->language->tag, $this->config->title]);
        if ($p === $state['path']) {
            $this->config::set('sort', $sort);
            $this->config::set('chunk', $chunk);
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
                if ($query = \l(\HTTP::get($this->config->q) ?? "")) {
                    $query = \explode(' ', $query);
                    $this->config::set('is.search', true);
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
            $this->config::set([
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
            $title = [$tag->title, $this->language->tag, $this->config->title];
            if ($query) {
                \array_unshift($title, $this->language->search . ': ' . \implode(' ', $query));
            }
            $pager = new \Pager\Pages($pages->vomit(), [$chunk, $i], $url . $path);
            $pages = $pages->chunk($chunk, $i)->map(function($v) {
                return new \Page($v);
            });
            $GLOBALS['page'] = $tag;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $pages;
            $GLOBALS['parent'] = $page;
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                $this->config::set('is.error', 404);
                $this->config::set('has', [
                    'next' => false,
                    'parent' => false,
                    'prev' => false
                ]);
                $this->view('404' . $path . '/' . $current);
            }
            $this->title($title);
            $this->config::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ]);
            $this->status(200);
            $this->view('pages' . $path . '/' . $current);
        }
    }
}

\Route::lot('*', __NAMESPACE__ . "\\tag", 20);