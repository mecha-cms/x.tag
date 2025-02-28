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
    function page__route($v) {
        if (!$path = $this->_exist()) {
            return $v;
        }
        \extract(\lot(), \EXTR_SKIP);
        if (0 === \strpos($path, \LOT . \D . 'tag' . \D)) {
            return '/' . \trim($state->x->tag->route ?? 'tag', '/') . '/' . $this->name;
        }
        return $v;
    }
    function page_query() {
        if (!$kind = $this->kind) {
            return [];
        }
        $r = [];
        foreach ((array) $kind as $v) {
            if ($name = \To::tag($v)) {
                $r[] = \strtr($name, '-', ' ');
            }
        }
        \sort($r);
        return $r;
    }
    function page_tags() {
        if (!$path = $this->_exist()) {
            return new \Tags;
        }
        if (!$query = $this->query()) {
            return new \Tags;
        }
        $folder = \dirname($path);
        $parent = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1) ?: null;
        $tags = [];
        foreach ((array) $query as $q) {
            $q = \strtr($q, ' ', '-');
            $tags[$q] = [
                'page' => $path,
                'parent' => $parent,
                'path' => \LOT . \D . 'tag' . \D . $q . '.page'
            ];
        }
        return (new \Tags($tags))->sort([1, 'title'], true);
    }
    function route__page($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        if ($part = \x\page\n($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        // For `/tag/:part`, `/tag/:name`, and `/tag/:name/:part`
        if (0 === \strpos($path . '/', $route . '/')) {
            return \Hook::fire('route.tag', [$content, $part ? '/' . $part : null, $query, $hash]);
        }
        if ($part && $path) {
            $a = \explode('/', $path);
            // For `/…/tag/:part`, and `/…/tag/:name/:part`
            if (\array_pop($a) === $route || \array_pop($a) === $route) {
                return \Hook::fire('route.tag', [$content, \implode('/', $a) . '/' . $part, $query, $hash]);
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
        if ($part = \x\page\n($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        $part = ($part ?? 0) - 1;
        $page = $tag->parent ?? $tag;
        $chunk = $tag->chunk ?? $page->chunk ?? 5;
        $deep = "" !== $path ? 0 : true;
        $sort = \array_replace([1, 'path'], (array) ($tag->sort ?? []), (array) ($page->sort ?? []));
        // For `/tag/:name`, `/tag/:name/:part`, and `/…/tag/:name/:part`
        if ($name = \State::get('[x].query.tag') ?? "") {
            if (null !== ($id = \From::tag($name))) {
                // For `/tag/:name`
                if ($part < 0 && "" === $path) {
                    \lot('page', $tag);
                    \lot('t')[] = \i('Tag');
                    \lot('t')[] = $tag->title;
                    \State::set([
                        'has' => [
                            'next' => false,
                            'prev' => false
                        ]
                    ]);
                    return ['page/tag/' . $name, [], 200];
                }
                $pages = \Pages::from(\LOT . \D . 'page' . ("" !== $path ? \D . $path : ""), 'page', $deep)->sort($sort);
                \State::set([
                    'chunk' => $chunk,
                    'count' => $count = $pages->count, // Total number of page(s) before chunk
                    'deep' => $deep,
                    'part' => $part + 1,
                    'sort' => $sort
                ]);
                if ($count > 0) {
                    $pages = $pages->is(function ($v) use ($id) {
                        return false !== \strpos(',' . \implode(',', (array) $v->kind) . ',', ',' . $id . ',');
                    });
                }
                \lot('t')[] = \i('Tags');
                \lot('t')[] = $tag->title;
                $pager = \Pager::from($pages);
                $pager->path = $path . '/' . $route . '/' . $name;
                \lot('page', $page);
                \lot('pager', $pager = $pager->chunk($chunk, $part));
                \lot('pages', $pages = $pages->chunk($chunk, $part));
                if (0 === $pages->count) { // Total number of page(s) after chunk
                    \State::set([
                        'has' => [
                            'next' => false,
                            'prev' => false
                        ],
                        'is' => [
                            'page' => false,
                            'pages' => true
                        ]
                    ]);
                    \lot('t')[] = \i('Error');
                    return ['pages/tag/' . $name, [], 404];
                }
                \State::set('has', [
                    'next' => !!$pager->next,
                    'pages' => true,
                    'parent' => !!$tag->parent,
                    'prev' => !!$pager->prev
                ]);
                return ['pages/tag/' . $name, [], 200];
            }
            return $content;
        }
        $chunk = $state->x->tag->chunk ?? 5;
        $deep = $state->x->tag->deep ?? 0;
        $sort = \array_replace([1, 'title'], (array) ($state->x->tag->sort ?? []));
        // For `/…/tag/:part`
        if ($path) {
            $folder = \LOT . \D . 'page' . \D . $path;
            $page = new \Page(\exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1) ?: null);
            $tags = [];
            foreach (\Pages::from($folder, 'page', $deep) as $v) {
                if (!$kind = $v->kind) {
                    continue;
                }
                foreach ((array) $kind as $k) {
                    if (!$k = \To::tag($k)) {
                        continue;
                    }
                    $k = \LOT . \D . 'tag' . \D . $k;
                    if (!$k = \exist([
                        $k . '.archive',
                        $k . '.page'
                    ], 1)) {
                        continue;
                    }
                    $tags[$k] = [
                        'parent' => $page->path,
                        'path' => $k
                    ];
                }
            }
            $pages = (new \Tags(\array_values($tags)))->sort($sort);
            if (0 === ($count = $pages->count)) {
                return $content;
            }
            \State::set('count', $count);
            $pager = \Pager::from($pages);
            $pager->hash = $hash;
            $pager->path = $path . '/' . $route;
            $pager->query = $query;
            \lot('page', $page);
            \lot('pager', $pager = $pager->chunk($chunk, $part));
            \lot('pages', $pages = $pages->chunk($chunk, $part));
            \lot('t')[] = $page->title;
            \lot('t')[] = \i('Tags');
            if (0 === $pages->count) { // Total number of page(s) after chunk
                \State::set([
                    'has' => [
                        'next' => false,
                        'page' => false,
                        'pages' => false,
                        'parent' => !!$page->parent,
                        'prev' => false
                    ],
                    'is' => [
                        'error' => 404,
                        'page' => false,
                        'pages' => true
                    ]
                ]);
                \lot('t')[] = \i('Error');
                return ['pages/tags/' . $path, [], 404];
            }
            \State::set([
                'has' => [
                    'next' => !!$pager->next,
                    'page' => true,
                    'pages' => true,
                    'parent' => !!$page->parent,
                    'prev' => !!$pager->prev
                ],
                'is' => [
                    'error' => false,
                    'page' => false,
                    'pages' => true
                ]
            ]);
            return ['pages/tags', [], 200];
        }
        // For `/tag/:part`
        $pages = \Pages::from(\LOT . \D . 'tag', 'page')->sort($sort);
        if (0 === ($count = $pages->count)) { // Total number of page(s) before chunk
            return $content;
        }
        \State::set('count', $count);
        $pager = \Pager::from($pages);
        $pager->hash = $hash;
        $pager->path = $route;
        $pager->query = $query;
        \lot('page', $page = new \Page([
            'description' => \i('List of the %s.', 'tags'),
            'exist' => true,
            'title' => \i('Tags'),
            'type' => 'HTML'
        ]));
        \lot('pager', $pager = $pager->chunk($chunk, $part));
        \lot('pages', $pages = $pages->chunk($chunk, $part));
        \lot('t')[] = $page->title;
        if (0 === $pages->count) { // Total number of page(s) after chunk
            \State::set([
                'has' => [
                    'next' => false,
                    'page' => false,
                    'pages' => false,
                    'parent' => true,
                    'prev' => false
                ],
                'is' => [
                    'error' => 404,
                    'page' => false,
                    'pages' => true
                ]
            ]);
            \lot('t')[] = \i('Error');
            return ['pages/tags', [], 404];
        }
        \State::set([
            'has' => [
                'next' => !!$pager->next,
                'page' => false,
                'pages' => true,
                'parent' => !!$page->parent,
                'prev' => !!$pager->prev
            ],
            'is' => [
                'error' => false,
                'page' => false,
                'pages' => true
            ]
        ]);
        return ['pages/tags', [], 200];
    }
    if ($part = \x\page\n($path = \trim($url->path ?? "", '/'))) {
        $path = \substr($path, 0, -\strlen('/' . $part));
    }
    $route = \trim($state->x->tag->route ?? 'tag', '/');
    // For `/tag/…`
    if (0 === \strpos($path . '/', $route . '/')) {
        \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
        \State::set([
            'has' => [
                'page' => false,
                'pages' => false,
                'parent' => false,
                'part' => $part >= 0
            ],
            'is' => [
                'error' => 404,
                'page' => !$part,
                'pages' => !!$part,
                'tag' => !$part,
                'tags' => !!$part
            ],
            'part' => $part
        ]);
        // For `/tag/:name/…`
        if ("" !== ($query = \substr($path, \strlen($route) + 1))) {
            \State::set('[x].query.tag', $query);
            $folder = \LOT . \D . 'tag' . \D . \strtr($query, '/', \D);
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \lot('tag', $tag = new \Tag($file));
                \State::set([
                    'has' => ['page' => true],
                    'is' => ['error' => false]
                ]);
            }
        }
    } else {
        $a = \explode('/', $path);
        $name = \array_pop($a);
        // For `/…/tag/:part`
        if ($a && $part && $name === $route) {
            \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
            \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
            \State::set([
                'has' => [
                    'page' => false,
                    'pages' => false,
                    'parent' => true,
                    'part' => true
                ],
                'is' => [
                    'error' => 404,
                    'page' => !$part,
                    'pages' => !!$part,
                    'tag' => !$part,
                    'tags' => !!$part
                ],
                'part' => $part
            ]);
        } else {
            $r = \array_pop($a);
            $folder = \LOT . \D . 'tag' . \D . $name;
            // For `/…/tag/:name/:part`
            if ($a && $part && $r === $route) {
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
                \State::set('[x].query.tag', $name);
                \State::set([
                    'has' => [
                        'page' => false,
                        'pages' => false,
                        'parent' => true,
                        'part' => true
                    ],
                    'is' => [
                        'error' => 404,
                        'page' => !$part,
                        'pages' => !!$part,
                        'tag' => !$part,
                        'tags' => !!$part
                    ],
                    'part' => $part
                ]);
                if ($file = \exist([
                    $folder . '.archive',
                    $folder . '.page'
                ], 1)) {
                    $folder = \LOT . \D . 'page' . \implode(\D, $a);
                    \lot('tag', new \Tag($file, [
                        'parent' => \exist([
                            $folder . '.archive',
                            $folder . '.page'
                        ], 1) ?: null
                    ]));
                    \State::set([
                        'has' => ['page' => true],
                        'is' => ['error' => false]
                    ]);
                }
            }
        }
    }
    \Hook::set('page.route', __NAMESPACE__ . "\\page__route", 100);
    \Page::_('query', __NAMESPACE__ . "\\page_query");
    \Page::_('tags', __NAMESPACE__ . "\\page_tags");
}