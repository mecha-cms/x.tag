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
        $parent = \exist(\dirname($path) . '.{' . ($x = \x\page\x()) . '}', 1) ?: null;
        $tags = [];
        foreach ((array) $kind as $k) {
            if (!$v = \To::tag($k)) {
                continue;
            }
            $tags[$v] = [
                'page' => $path,
                'parent' => $parent,
                'path' => \exist(\LOT . \D . 'tag' . \D . '{#,}' . $v . '.{' . $x . '}', 1)
            ];
        }
        return new \Tags($tags);
    }
    function route__page($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        if ($part = \x\page\part($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        // No part of the path can start with a `#` or `~`
        $test = '/' . \rawurldecode($path);
        if (false !== \strpos('/' . $path, "/'") || false !== \strpos('/' . $path, '/~')) {
            return [
                'lot' => [],
                'status' => 404,
                'y' => 'page'
            ];
        }
        \extract(\lot(), \EXTR_SKIP);
        $route = \trim($state->x->tag->route ?? 'tag', '/');
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
            if ($route === \array_pop($a) && \exist(\LOT . \D . 'tag' . \D . '{#,}' . \rawurldecode($v) . '.{' . \x\page\x() . '}', 1)) {
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
        if ($part = \x\page\part($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        $part = ($part ?? 0) - 1;
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        $x = \x\page\x();
        // For `/…/tag/:part`, and `/…/tag/:name/:part`
        if ($part >= 0 && $path) {
            if ($file = \exist(\LOT . \D . 'page' . \D . \rawurldecode($path) . '.{' . $x . '}', 1)) {
                \lot('page', $page = new \Page($file));
                // For `/…/tag/:name/:part`
                if ($name = $state->q('tag.name')) {
                    if (null === ($id = \From::tag($name))) {
                        return $content;
                    }
                    $chunk = $tag->chunk ?? $page->chunk ?? 5;
                    $sort = \array_replace([1, 'path'], (array) ($page->sort ?? []), (array) ($tag->sort ?? []));
                    $pages = $page->children($x, true)->is(function ($v) use ($id) {
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
                    return [
                        'lot' => [],
                        'status' => 0 === $count ? 404 : 200,
                        'y' => 'pages/tag/' . $name
                    ];
                }
                // For `/…/tag/:part`
                $tags = [];
                $chunk = $state->x->tag->page->chunk ?? $page->chunk ?? 5;
                $sort = \array_replace([1, 'path'], (array) ($page->sort ?? []), (array) ($state->x->tag->page->sort ?? []));
                if ($pages = $page->children($x, true)) {
                    foreach ($pages as $v) {
                        if (!$k = $v->kind) {
                            continue;
                        }
                        foreach ((array) $k as $kk) {
                            if (!$kk = \To::tag($kk)) {
                                continue;
                            }
                            $tags[$kk] = [
                                'parent' => $page->path,
                                'path' => \exist(\LOT . \D . 'tag' . \D . '{#,}' . $kk . '.{' . $x . '}', 1)
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
                return [
                    'lot' => [],
                    'status' => 0 === $count ? 404 : 200,
                    'y' => 'pages/tag'
                ];
            }
            return $content;
        }
        // For `/tag/:name`, and `/tag/:name/:part`
        if ($name = $state->q('tag.name')) {
            if (null === ($id = \From::tag($name))) {
                return $content;
            }
            \lot('page', $tag);
            if ($file = \exist(\LOT . \D . 'tag' . \D . '{#,}' . $name . '.{' . $x . '}', 1)) {
                $chunk = $tag->chunk ?? 5;
                $sort = \array_replace([1, 'path'], (array) ($tag->sort ?? []));
                $pages = \Pages::from(\LOT . \D . 'page' . \rawurldecode("" !== $path ? \D . $path : ""), $x, true)->is(function ($v) use ($id) {
                    return false !== \strpos(',' . \implode(',', (array) $v->kind) . ',', ',' . $id . ',');
                })->sort($sort);
                // For `/tag/:name`
                if ($part < 0) {
                    \lot('t')[] = \i('Tag');
                    \lot('t')[] = $tag->title;
                    \State::set('has.pages', \q($pages) > 0);
                    return [
                        'lot' => [],
                        'status' => 200,
                        'y' => 'page/tag/' . $name
                    ];
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
                return [
                    'lot' => [],
                    'status' => 0 === $count ? 404 : 200,
                    'y' => 'pages/tag/' . $name
                ];
            }
            return $content;
        }
        $chunk = $state->x->tag->page->chunk ?? 5;
        $deep = $state->x->tag->page->deep ?? 0;
        $sort = \array_replace([1, 'path'], (array) ($state->x->tag->page->sort ?? []));
        // For `/tag/:part`
        $pages = \Tags::from(\LOT . \D . 'tag', $x)->sort($sort);
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
        return [
            'lot' => [],
            'status' => 0 === $count ? 404 : 200,
            'y' => 'pages/tags'
        ];
    }
    if ($part = \x\page\part($path = \trim($url->path ?? "", '/'))) {
        $path = \substr($path, 0, -\strlen('/' . $part));
    }
    $part = ($part ?? 0) - 1;
    $route = \trim($state->x->tag->route ?? 'tag', '/');
    $x = \x\page\x();
    // For `/tag/…`
    if (0 === \strpos($path . '/', $route . '/')) {
        \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
        \State::set([
            'is' => [
                'tag' => $part < 0 && $path !== $route,
                'tags' => $part >= 0
            ]
        ]);
        // For `/tag/:name/…`
        if ("" !== ($v = \substr($path, \strlen($route) + 1))) {
            if ($file = \exist(\LOT . \D . 'tag' . \D . '{#,}' . \rawurldecode(\strtr($v, '/', \D)) . '.{' . $x . '}', 1)) {
                \lot('tag', $tag = new \Tag($file));
            }
            // A tag does not need to exist in order to declare route query data. Given the subjective nature of this
            // method, it is up to the developer of the extension to keep track of the data for later use. It can then
            // be used across the route since Mecha lacks a native mechanism to collect route information.
            \State::set([
                'q' => [
                    'tag' => [
                        'name' => $v,
                        'part' => $part >= 0 ? $part + 1 : null
                    ]
                ]
            ]);
        }
    } else {
        $a = \explode('/', $path);
        $v = \array_pop($a);
        // For `/…/tag/:part`
        if ($a && $part >= 0 && $v === $route) {
            if (\exist(\LOT . \D . 'page' . \D . \rawurldecode(\implode(\D, $a)) . '.{' . $x . '}', 1)) {
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
            }
            \State::set([
                'is' => [
                    'tag' => false,
                    'tags' => true
                ]
            ]);
        } else {
            $r = \array_pop($a);
            // For `/…/tag/:name/:part`
            if ($a && $part >= 0 && $r === $route) {
                if ($file = \exist(\LOT . \D . 'tag' . \D . '{#,}' . \rawurldecode($v) . '.{' . $x . '}', 1)) {
                    \lot('tag', new \Tag($file, [
                        'parent' => \exist(\LOT . \D . 'page' . \D . \rawurldecode(\implode(\D, $a)) . '.{' . $x . '}', 1) ?: null
                    ]));
                }
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
                \State::set([
                    'is' => [
                        'tag' => false,
                        'tags' => true
                    ],
                    'q' => [
                        'tag' => [
                            'name' => $v,
                            'part' => $part + 1
                        ]
                    ]
                ]);
            }
        }
    }
    \Page::_('tags', __NAMESPACE__ . "\\page_tags");
}