<?php

namespace {
    function tag(...$lot) {
        return \Tag::from(...$lot);
    }
    function tags(...$lot) {
        return \Tags::from(...$lot);
    }
}

namespace x\tag {
    function route($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract($GLOBALS, \EXTR_SKIP);
        $name = \From::query($query)['name'] ?? "";
        $path = \trim($path ?? "", '/');
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        if ($path && \preg_match('/^(?:(.*?)\/)?([1-9]\d*)$/', $path, $m)) {
            [$any, $path, $part] = $m;
        }
        $part = ((int) ($part ?? 1)) - 1;
        if (null !== ($id = \From::tag($name))) {
            $route_default = \trim($state->route ?? 'index', '/');
            $page = $tag->parent ?? new \Page(\exist([
                \LOT . \D . 'page' . \D . $route_default . '.archive',
                \LOT . \D . 'page' . \D . $route_default . '.page'
            ]) ?: null);
            $chunk = $tag->chunk ?? $page->chunk ?? 5;
            $deep = "" !== $path ? ($tag->deep ?? $page->deep ?? 0) : true;
            $sort = $tag->sort ?? $page->sort ?? [1, 'path'];
            $pages = \Pages::from(\LOT . \D . 'page' . ("" !== $path ? \D . $path : ""), 'page', $deep)->sort($sort);
            \State::set([
                'chunk' => $chunk,
                'count' => $count = $pages->count, // Total number of page(s)
                'deep' => $deep,
                'part' => $part + 1,
                'sort' => $sort
            ]);
            if ($count > 0) {
                $pages = $pages->is(function ($v) use ($id) {
                    return false !== \strpos(',' . \implode(',', (array) $v->kind) . ',', ',' . $id . ',');
                });
            }
            \State::set([
                'is' => [
                    'error' => false,
                    'page' => false,
                    'pages' => true,
                    'tag' => false, // Never be `true`
                    'tags' => true
                ],
                'has' => [
                    'page' => true,
                    'pages' => $count > 0,
                    'parent' => true
                ]
            ]);
            $GLOBALS['t'][] = \i('Tag');
            $GLOBALS['t'][] = $tag->title;
            $pager = \Pager::from($pages);
            $pager->path = $path . '/' . $route . '/' . $name;
            $GLOBALS['page'] = $page;
            $GLOBALS['pager'] = $pager = $pager->chunk($chunk, $part);
            $GLOBALS['pages'] = $pages = $pages->chunk($chunk, $part);
            $count = $pages->count; // Total number of page(s) after chunk
            if (0 === $count) {
                // Greater than the maximum part or less than `1`, abort!
                \State::set([
                    'has' => [
                        'next' => false,
                        'parent' => false,
                        'prev' => false
                    ],
                    'is' => [
                        'error' => 404,
                        'page' => true,
                        'pages' => false
                    ]
                ]);
                $GLOBALS['t'][] = \i('Error');
                return ['page', [], 404];
            }
            \State::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$tag->parent,
                'part' => !!($part + 1),
                'prev' => !!$pager->prev
            ]);
            return ['pages', [], 200];
        }
        \State::set([
            'has' => [
                'next' => false,
                'parent' => false,
                'prev' => false
            ],
            'is' => [
                'error' => 404,
                'page' => true,
                'pages' => false
            ]
        ]);
        $GLOBALS['t'][] = \i('Error');
        return ['page', [], 404];
    }
    function query() {
        $query = [];
        foreach ((array) $this->kind as $v) {
            if ($name = \To::tag($v)) {
                $query[] = \strtr($name, '-', ' ');
            }
        }
        \sort($query);
        return $query;
    }
    function tags() {
        $folder = \dirname($path = $this->path);
        $parent = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1) ?: null;
        $tags = [];
        foreach ($this->query() as $v) {
            $v = \strtr($v, ' ', '-');
            $tags[$v] = [
                'page' => $path,
                'parent' => $parent,
                'path' => \LOT . \D . 'tag' . \D . $v . '.page'
            ];
        }
        return (new \Tags($tags))->sort([1, 'title'], true);
    }
    \Page::_('query', __NAMESPACE__ . "\\query");
    \Page::_('tags', __NAMESPACE__ . "\\tags");
    $chops = \explode('/', $url->path ?? "");
    $part = \array_pop($chops);
    $tag = \array_pop($chops);
    $route = \trim($state->x->tag->route ?? 'tag', '/');
    $prefix = \array_pop($chops);
    $GLOBALS['tag'] = new \Tag;
    if ($tag && $route === $prefix && ($file = \exist([
        \LOT . \D . 'tag' . \D . $tag . '.archive',
        \LOT . \D . 'tag' . \D . $tag . '.page'
    ], 1))) {
        $folder = \LOT . \D . 'page' . ($path = \implode(\D, $chops));
        $GLOBALS['tag'] = new \Tag($file, [
            'parent' => "" !== $path ? (\exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1) ?: null) : null
        ]);
        \Hook::set('route.page', function ($content, $path, $query, $hash) use ($route) {
            // Return the route value to the native page route and move the tag route parameter to `name`
            if ($path && \preg_match('/^(.*?)\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
                [$any, $path, $name, $part] = $m;
                $query = \To::query(\array_replace(\From::query($query), ['name' => $name]));
                return \Hook::fire('route.tag', [$content, $path . '/' . $part, $query, $hash]);
            }
            // List page(s) recursively if `/tag/:name` pattern appears at the root
            if ($path && \preg_match('/^\/' . x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
                [$any, $name, $part] = $m;
                $query = \To::query(\array_replace(\From::query($query), ['name' => $name]));
                return \Hook::fire('route.tag', [$content, '/' . $part, $query, $hash]);
            }
            return $content;
        }, 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route", 100);
    }
}