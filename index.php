<?php

namespace {
    function tag(...$lot) {
        return \Tag::from(...$lot);
    }
    function tags(...$lot) {
        return \Tags::from(...$lot);
    }
    // Initialize response variable(s)
    \lot('tag', new \Tag);
}

namespace x\tag {
    function route__page($content, $path, $query, $hash) {
        \extract(\lot(), \EXTR_SKIP);
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        // Return the route value to the native page route and move the tag route parameter to `name`
        if ($path && \preg_match('/^(.*?)\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
            [$any, $path, $name, $part] = $m;
            $query = \To::query(\array_replace(\From::query($query), ['name' => $name]));
            return \Hook::fire('route.tag', [$content, $path . '/' . $part, $query, $hash]);
        }
        // List page(s) recursively if `/tag/:name` route is in the root URL
        if ($path && \preg_match('/^\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
            [$any, $name, $part] = $m;
            $query = \To::query(\array_replace(\From::query($query), ['name' => $name]));
            return \Hook::fire('route.tag', [$content, '/' . $part, $query, $hash]);
        }
        return $content;
    }
    function route__tag($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        $name = \From::query($query)['name'] ?? "";
        $path = \trim($path ?? "", '/');
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        if ($path && \preg_match('/^(?:(.*?)\/)?([1-9]\d*)$/', $path, $m)) {
            [$any, $path, $part] = $m;
        }
        $part = ((int) ($part ?? 1)) - 1;
        if (null !== ($id = \From::tag($name))) {
            $folder = \LOT . \D . 'page' . \D . \trim($state->route ?? 'index', '/');
            $page = $tag->parent ?? new \Page(\exist([
                $folder . '.archive',
                $folder . '.page'
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
            \lot('t')[] = \i('Tag');
            \lot('t')[] = $tag->title;
            $pager = \Pager::from($pages);
            $pager->path = $path . '/' . $route . '/' . $name;
            \lot('page', $page);
            \lot('pager', $pager = $pager->chunk($chunk, $part));
            \lot('pages', $pages = $pages->chunk($chunk, $part));
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
                \lot('t')[] = \i('Error');
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
        \lot('t')[] = \i('Error');
        return ['page', [], 404];
    }
    function query() {
        if (!$kind = $this->kind) {
            return [];
        }
        $query = [];
        foreach ((array) $kind as $v) {
            if ($name = \To::tag($v)) {
                $query[] = \strtr($name, '-', ' ');
            }
        }
        \sort($query);
        return $query;
    }
    function tags() {
        if (!$query = $this->query()) {
            return new \Tags;
        }
        $folder = \dirname($path = $this->path);
        $parent = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1) ?: null;
        $tags = [];
        foreach ((array) $query as $v) {
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
    $any = \explode('/', $url->path ?? "");
    $part = \array_pop($any);
    $tag = \array_pop($any);
    $route = \trim($state->x->tag->route ?? 'tag', '/');
    $prefix = \array_pop($any);
    $folder = \LOT . \D . 'tag' . \D . $tag;
    if ($tag && $route === $prefix && ($file = \exist([
        $folder . '.archive',
        $folder . '.page'
    ], 1))) {
        $folder = \LOT . \D . 'page' . ($path = \implode(\D, $any));
        \lot('tag', new \Tag($file, [
            'parent' => "" !== $path ? (\exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1) ?: null) : null
        ]));
        \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route__tag", 100);
    }
}