<?php

namespace x\tag {
    function route($content, $path, $query, $hash, $r) {
        if (null !== $content) {
            return $content;
        }
        \extract($GLOBALS, \EXTR_SKIP);
        $name = $r['name'];
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
                $pages->lot($pages->is(static function($v) use($id) {
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
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], new \Page(null, [
                'link' => $url . '/' . $path . '/' . $route . '/' . $name
            ]));
            // Set proper parent link
            $pager->parent = $i > 0 ? new \Page(null, ['link' => $url . '/' . $path . '/' . $route . '/' . $name . '/1']) : $page;
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
                return ['page', [], 404];
            }
            \State::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'part' => $i + 1,
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
        $tags = [];
        foreach ($this->query() as $v) {
            $v = \strtr($v, ' ', '-');
            $tags[$v] = \LOT . \D . 'tag' . \D . $v . '.page';
        }
        $tags = new \Tags($tags);
        $folder = \dirname($this->path);
        $tags->page = $this;
        if ($path = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            $tags->parent = new \Page($path);
        }
        return $tags->sort([1, 'title']);
    }
    \Page::_('query', __NAMESPACE__ . "\\query");
    \Page::_('tags', __NAMESPACE__ . "\\tags");
    $chops = \explode('/', $url->path ?? "");
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
        $folder = \LOT . \D . 'page' . \implode(\D, $chops);
        // $tag->page = null;
        if ($path = \exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            $tag->parent = new \Page($path);
        }
        $GLOBALS['tag'] = $tag;
        \Hook::set('route.page', function($content, $path, $query, $hash) use($route) {
            if ($path && \preg_match('/^(.*?)\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
                [$any, $path, $name, $i] = $m;
                $r['name'] = $name;
                return \Hook::fire('route.tag', [$content, $path, $query, $hash, $r]);
            }
            return $content;
        }, 90);
        \Hook::set('route.tag', __NAMESPACE__ . "\\route", 100);
    }
}