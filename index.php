<?php

namespace {
    function tag(...$lot) {
        return new \Tag(...$lot);
    }
    function tags(...$lot) {
        return new \Tags(...$lot);
    }
    // Initialize layout variable(s)
    \lot('tag', new \Tag);
}

namespace x\tag {
    function page_tags() {
        if (!$path = $this->path) {
            return null;
        }
        if (!$kind = $this->kind) {
            return null;
        }
        $r = \dirname($path);
        $parent = \exist([
            $r . '.archive',
            $r . '.page'
        ], 1) ?: null;
        $tags = [];
        foreach ((array) $kind as $k) {
            if (!$v = \To::tag($k)) {
                continue;
            }
            $folder = \LOT . \D . 'tag' . \D . $v;
            $tags[$k] = [
                'page' => $path,
                'parent' => $parent,
                'path' => \exist([
                    $folder . '.archive',
                    $folder . '.page'
                ], 1)
            ];
        }
        return new \Tags($tags);
    }
    function route__page($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        if ($part = \x\page\part($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        // For `/tag`
        if (!$part && $path === $route) {
            return $content;
        }
        // For `/tag/:part`, `/tag/:name`, and `/tag/:name/:part`
        if (0 === \strpos($path . '/', $route . '/')) {
            return \Hook::fire('route.tag', [$content, $part ? '/' . $part : null, $query, $hash]);
        }
        if ($part && $path) {
            $a = \explode('/', $path);
            // For `/…/tag/:part`
            if ($route === ($v = \array_pop($a))) {
                return \Hook::fire('route.tag', [$content, ($a ? '/' . \implode('/', $a) : "") . '/' . $part, $query, $hash]);
            }
            // For `/…/tag/:name/:part`
            $folder = \LOT . \D . 'tag' . \D . $v;
            if ($route === \array_pop($a) && \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                return \Hook::fire('route.tag', [$content, ($a ? '/' . \implode('/', $a) : "") . '/' . $part, $query, $hash]);
            }
        }
        return $content;
    }
    function route__tag($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        if ($part = \x\page\part($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        $part = ($part ?? 0) - 1;
        // For `/…/tag/:part`, and `/…/tag/:name/:part`
        if ($part >= 0 && $path) {
            $folder = \LOT . \D . 'page' . \D . $path;
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \lot('page', $page = new \Page($file));
                // For `/…/tag/:name/:part`
                if ($name = \State::get('[x].query.tag') ?? "") {
                    if (null === ($id = \From::tag($name))) {
                        return $content;
                    }
                    $chunk = $tag->chunk ?? $page->chunk ?? 5;
                    $sort = \array_replace([1, 'path'], (array) ($page->sort ?? []), (array) ($tag->sort ?? []));
                    $pages = $page->children('page', true)->is(function ($v) use ($id) {
                        return false !== \strpos(',' . \implode(',', (array) $v->kind) . ',', ',' . $id . ',');
                    })->sort($sort);
                    \lot('t')[] = $page->title;
                    \lot('t')[] = \i('Tag');
                    \lot('t')[] = $tag->title;
                    \lot('t')[] = \i('Pages');
                    $pager = \Pager::from($pages);
                    $pager->path = $path . '/' . $route . '/' . $name;
                    \lot('pager', $pager = $pager->chunk($chunk, $part));
                    \lot('pages', $pages = $pages->chunk($chunk, $part));
                    if (0 === ($count = \q($pages))) {
                        \lot('t')[] = \i('Error');
                    }
                    \State::set([
                        'has' => [
                            'next' => !!$pager->next,
                            'parent' => !!$page->parent,
                            'prev' => !!$pager->prev
                        ],
                        'is' => ['error' => 0 === $count ? 404 : false],
                        'with' => ['pages' => $count > 0]
                    ]);
                    return ['pages/tag/' . $name, [], 0 === $count ? 404 : 200];
                }
                // For `/…/tag/:part`
                $tags = [];
                $chunk = $state->x->tag->page->chunk ?? $page->chunk ?? 5;
                $sort = \array_replace([1, 'path'], (array) ($page->sort ?? []), (array) ($state->x->tag->page->sort ?? []));
                if ($pages = $page->children('page', true)) {
                    foreach ($pages as $v) {
                        if (!$k = $v->kind) {
                            continue;
                        }
                        foreach ((array) $k as $kk) {
                            if (!$kk = \To::tag($kk)) {
                                continue;
                            }
                            $folder = \LOT . \D . 'tag' . \D . $kk;
                            $tags[$kk] = [
                                'parent' => $page->path,
                                'path' => \exist([
                                    $folder . '.archive',
                                    $folder . '.page'
                                ], 1)
                            ];
                        }
                    }
                }
                $tags = \Tags::from(\array_values($tags))->sort($sort);
                \lot('t')[] = $page->title;
                \lot('t')[] = \i('Tags');
                $pager = \Pager::from($tags);
                $pager->path = $path . '/' . $route;
                \lot('pager', $pager = $pager->chunk($chunk, $part));
                \lot('pages', $tags = $tags->chunk($chunk, $part));
                if (0 === ($count = \q($tags))) {
                    \lot('t')[] = \i('Error');
                }
                \State::set([
                    'has' => [
                        'next' => !!$pager->next,
                        'parent' => !!$page->parent,
                        'prev' => !!$pager->prev
                    ],
                    'is' => ['error' => 0 === $count ? 404 : false],
                    'with' => ['tags' => $count > 0]
                ]);
                return ['pages/tag', [], 0 === $count ? 404 : 200];
            }
            return $content;
        }
        // For `/tag/:name`, and `/tag/:name/:part`
        if ($name = \State::get('[x].query.tag') ?? "") {
            if (null === ($id = \From::tag($name))) {
                return $content;
            }
            \lot('page', $tag);
            $folder = \LOT . \D . 'tag' . \D . $name;
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                $chunk = $tag->chunk ?? 5;
                $sort = \array_replace([1, 'path'], (array) ($tag->sort ?? []));
                $pages = \Pages::from(\LOT . \D . 'page' . ("" !== $path ? \D . $path : ""), 'page', true)->is(function ($v) use ($id) {
                    return false !== \strpos(',' . \implode(',', (array) $v->kind) . ',', ',' . $id . ',');
                })->sort($sort);
                // For `/tag/:name`
                if ($part < 0) {
                    \lot('t')[] = \i('Tag');
                    \lot('t')[] = $tag->title;
                    \State::set('has.pages', \q($pages) > 0);
                    return ['page/tag/' . $name, [], 200];
                }
                // For `/tag/:name/:part`
                \lot('t')[] = \i('Tag');
                \lot('t')[] = $tag->title;
                \lot('t')[] = \i('Pages');
                $pager = \Pager::from($pages);
                $pager->path = $path . '/' . $route . '/' . $name;
                \lot('pager', $pager = $pager->chunk($chunk, $part));
                \lot('pages', $pages = $pages->chunk($chunk, $part));
                if (0 === ($count = \q($pages))) {
                    \lot('t')[] = \i('Error');
                }
                \State::set([
                    'has' => [
                        'next' => !!$pager->next,
                        'parent' => !!$page->parent,
                        'prev' => !!$pager->prev
                    ],
                    'is' => ['error' => 0 === $count ? 404 : false],
                    'with' => ['pages' => $count > 0]
                ]);
                return ['pages/tag/' . $name, [], 0 === $count ? 404 : 200];
            }
            return $content;
        }
        $chunk = $state->x->tag->page->chunk ?? 5;
        $deep = $state->x->tag->page->deep ?? 0;
        $sort = \array_replace([1, 'path'], (array) ($state->x->tag->page->sort ?? []));
        // For `/tag/:part`
        $pages = \Tags::from(\LOT . \D . 'tag', 'page')->sort($sort);
        $pager = \Pager::from($pages);
        $pager->hash = $hash;
        $pager->path = $route;
        $pager->query = $query;
        \lot('page', $page = new \Page([
            'description' => \i('List of site %s.', 'tags'),
            'exist' => true,
            'title' => \i('Tags'),
            'type' => 'HTML'
        ]));
        \lot('pager', $pager = $pager->chunk($chunk, $part));
        \lot('pages', $pages = $pages->chunk($chunk, $part));
        \lot('t')[] = $page->title;
        if (0 === ($count = \q($pages))) {
            \lot('t')[] = \i('Error');
        }
        \State::set([
            'has' => [
                'next' => !!$pager->next,
                'parent' => !!$page->parent,
                'prev' => !!$pager->prev
            ],
            'is' => ['error' => 0 === $count ? 404 : false],
            'with' => ['tags' => $count > 0]
        ]);
        return ['pages/tags', [], 0 === $count ? 404 : 200];
    }
    if ($part = \x\page\part($path = \trim($url->path ?? "", '/'))) {
        $path = \substr($path, 0, -\strlen('/' . $part));
    }
    $part = ($part ?? 0) - 1;
    $route = \trim($state->x->tag->route ?? 'tag', '/');
    // For `/tag/…`
    if (0 === \strpos($path . '/', $route . '/')) {
        \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
        \State::set('is', [
            'tag' => $part < 0 && $path !== $route,
            'tags' => $part >= 0
        ]);
        // For `/tag/:name/…`
        if ("" !== ($v = \substr($path, \strlen($route) + 1))) {
            \State::set('[x].query.tag', $v);
            $folder = \LOT . \D . 'tag' . \D . \strtr($v, '/', \D);
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \lot('tag', $tag = new \Tag($file));
            }
        }
    } else {
        $a = \explode('/', $path);
        $v = \array_pop($a);
        // For `/…/tag/:part`
        if ($a && $part >= 0 && $v === $route) {
            $folder = \LOT . \D . 'page' . \D . \implode(\D, $a);
            if (\exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
                \State::set('is', [
                    'tag' => false,
                    'tags' => true
                ]);
            }
        } else {
            $r = \array_pop($a);
            $folder = \LOT . \D . 'tag' . \D . $v;
            // For `/…/tag/:name/:part`
            if ($a && $part >= 0 && $r === $route) {
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
                \State::set('[x].query.tag', $v);
                \State::set('is', [
                    'tag' => false,
                    'tags' => true
                ]);
                if ($file = \exist([
                    $folder . '.archive',
                    $folder . '.page'
                ], 1)) {
                    $folder = \LOT . \D . 'page' . \D . \implode(\D, $a);
                    \lot('tag', new \Tag($file, [
                        'parent' => \exist([
                            $folder . '.archive',
                            $folder . '.page'
                        ], 1) ?: null
                    ]));
                }
            }
        }
    }
    \Page::_('tags', __NAMESPACE__ . "\\page_tags");
}