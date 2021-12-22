<?php

namespace x\tag {
    function route($name, $path, $query, $hash) {
        \extract($GLOBALS, \EXTR_SKIP);
        $path = \trim($path ?? "", '/');
        $route = \trim($state->x->tag->route ?? 'tag', '/');
        if ($path && \preg_match('/^(.*?)\/([1-9]\d*)$/', $path, $m)) {
            [$any, $path, $i] = $m;
        }
        $i = ((int) ($i ?? 1)) - 1;
        if (null !== ($id = \From::tag($name))) {
            $page = $tag->parent ?? new \Page;
            \State::set([
                'chunk' => $chunk = $tag['chunk'] ?? $page['chunk'] ?? 5,
                'deep' => $deep = $tag['deep'] ?? $page['deep'] ?? 0,
                'sort' => $sort = $tag['sort'] ?? $page['sort'] ?? [1, 'path']
            ]);
            $pages = \Pages::from(\LOT . \D . 'page' . \D . $path, 'page', $deep)->sort($sort);
            if ($pages->count() > 0) {
                $pages->lot($pages->is(function($v) use($id) {
                    $page = new \Page($v);
                    $k = ',' . \implode(',', (array) $page->kind) . ',';
                    return false !== \strpos($k, ',' . $id . ',');
                })->get());
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
                    'pages' => $pages->count() > 0,
                    'parent' => true
                ]
            ]);
            $GLOBALS['t'][] = \i('Tag');
            $GLOBALS['t'][] = $tag->title;
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], (object) [
                'link' => $url . '/' . $path . '/' . $route . '/' . $name
            ]);
            // Set proper parent link
            $pager->parent = $page;
            $pages = $pages->chunk($chunk, $i);
            $GLOBALS['page'] = $page;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $pages;
            if (0 === $pages->count()) {
                // Greater than the maximum step or less than `1`, abort!
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
                \status(404);
                \Hook::fire('layout', ['error/' . $path . '/' . $route . '/' . $name . '/' . ($i + 1)]);
            }
            \State::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ]);
            \status(200);
            \Hook::fire('layout', ['pages/' . $path . '/' . $route . '/' . $name . '/' . ($i + 1)]);
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
        \status(404);
        \Hook::fire('layout', ['error/' . $path . '/' . $route . '/' . $name . '/' . ($i + 1)]);
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
        $tags = [];
        foreach ($this->query() as $v) {
            $v = \strtr($v, ' ', '-');
            $tags[$v] = \LOT . \D . 'tag' . \D . $v . '.page';
        }
        $tags = new \Tags($tags);
        $tags->page = $this;
        $folder = \dirname($this->path);
        if ($file = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            $tags->parent = new \Page($file);
        }
        return $tags->sort([1, 'title']);
    }
    \Page::_('query', __NAMESPACE__ . "\\query");
    \Page::_('tags', __NAMESPACE__ . "\\tags");
    $chops = \explode('/', $url->path);
    $i = \array_pop($chops);
    $tag = \array_pop($chops);
    $route = \trim($state->x->tag->route ?? 'tag', '/');
    $prefix = \array_pop($chops);
    $GLOBALS['tag'] = new \Tag;
    if ($tag && $route === $prefix && ($file = \exist([
        \LOT . \D . 'tag' . \D . $tag . '.archive',
        \LOT . \D . 'tag' . \D . $tag . '.page'
    ], 1))) {
        $tag = new \Tag($file);
        // $tag->page = null;
        $folder = \LOT . \D . 'page' . \implode(\D, $chops);
        if ($page = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            $tag->parent = new \Page($page);
        }
        $GLOBALS['tag'] = $tag;
        \Hook::set('route.page', function($path, $query, $hash) use($route) {
            if ($path && \preg_match('/^(.*?)\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
                [$any, $path, $name, $i] = $m;
                \Hook::fire('route.tag', [$name, $path . '/' . $i, $query, $hash]);
            }
        }, 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route", 100);
    }
}