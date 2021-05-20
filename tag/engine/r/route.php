<?php namespace x\tag;

function route($any, $name) {
    extract($GLOBALS, \EXTR_SKIP);
    $i = ($url['i'] ?? 1) - 1;
    $path = $state->x->tag->path ?? '/tag';
    if (null !== ($id = \From::tag($name))) {
        $page = $tag->page ?? new \Page;
        \State::set([
            'chunk' => $chunk = $tag['chunk'] ?? $page['chunk'] ?? 5,
            'deep' => $deep = $tag['deep'] ?? $page['deep'] ?? 0,
            'sort' => $sort = $tag['sort'] ?? $page['sort'] ?? [1, 'path']
        ]);
        $pages = \Pages::from(\LOT . \DS . 'page' . \DS . $any, 'page', $deep)->sort($sort);
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
            'link' => $url . '/' . $any . $path . '/' . $name
        ]);
        // Set proper parent link
        if (0 === $i) {
            $pager->parent = $page;
        }
        $pages = $pages->chunk($chunk, $i);
        $GLOBALS['page'] = $page;
        $GLOBALS['pager'] = $pager;
        $GLOBALS['pages'] = $pages;
        $GLOBALS['parent'] = $page;
        if (0 === $pages->count()) {
            // Greater than the maximum step or less than `1`, abort!
            \State::set([
                'has' => [
                    'next' => false,
                    'parent' => false,
                    'prev' => false
                ],
                'is' => ['error' => 404]
            ]);
            $GLOBALS['t'][] = \i('Error');
            $this->layout('404/' . $any . $path . '/' . $name . '/' . ($i + 1));
        }
        \State::set('has', [
            'next' => !!$pager->next,
            'parent' => !!$pager->parent,
            'prev' => !!$pager->prev
        ]);
        $this->layout('pages/' . $any . $path . '/' . $name . '/' . ($i + 1));
    }
    \State::set([
        'has' => [
            'next' => false,
            'parent' => false,
            'prev' => false
        ],
        'is' => ['error' => 404]
    ]);
    $GLOBALS['t'][] = \i('Error');
    $this->layout('404/' . $any . $path . '/' . $name . '/' . ($i + 1));
}

\Route::set('*' . ($state->x->tag->path ?? '/tag') . '/:tag', 200, __NAMESPACE__ . "\\route", 10);
