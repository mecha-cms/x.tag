<?php namespace _\lot\x\tag;

$GLOBALS['tag'] = new \Tag;

function route($form) {
    global $language, $state, $url;
    $path = $this[0];
    $i = ($url['i'] ?? 1) - 1;
    $chops = \explode('/', $path);
    $n = \array_pop($chops); // The tag name
    $p = '/' . \array_pop($chops); // The tag path
    // Get tag ID from tag name…
    if (null !== ($id = \From::tag($n))) {
        $GLOBALS['t'][] = $language->tag;
        if ($p === \State::get('x.tag.path')) {
            $path = \implode('/', $chops);
            $r = \PAGE . \DS . $path;
            if ($file = \File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new \Page($file);
            }
            if ($file = \File::exist([
                \TAG . \DS . $n . '.page',
                \TAG . \DS . $n . '.archive'
            ])) {
                $tag = new \Tag($file);
            }
            \State::set([
                'chunk' => $chunk = $tag['chunk'] ?? $page['chunk'] ?? 5,
                'deep' => $deep = $tag['deep'] ?? $page['deep'] ?? 0,
                'sort' => $sort = $tag['sort'] ?? $page['sort'] ?? [1, 'path']
            ]);
            $pages = \Pages::from($r, 'page', $deep)->sort($sort);
            if ($pages->count() > 0) {
                $pages = $pages->is(function($v) use($id) {
                    if (\is_file($k = \Path::F($v) . \DS . 'kind.data')) {
                        $k = \e(\file_get_contents($k));
                    } else if (!$k = (\From::page(\file_get_contents($v))['kind'] ?? null)) {
                        return false;
                    }
                    $k = ',' . \implode(',', (array) $k) . ',';
                    return \strpos($k, ',' . $id . ',') !== false;
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
                    'pages' => $pages->count() > 0,
                    'parent' => true
                ]
            ]);
            $path = '/' . $path . $p . '/' . $n;
            $GLOBALS['t'][] = $tag->title;
            $pager = new \Pager\Pages($pages->get(), [$chunk, $i], $url . $path);
            $pages = $pages->chunk($chunk, $i);
            $GLOBALS['page'] = $page;
            $GLOBALS['pager'] = $pager;
            $GLOBALS['pages'] = $pages;
            $GLOBALS['parent'] = $page;
            $GLOBALS['tag'] = $tag;
            if ($pages->count() === 0) {
                // Greater than the maximum step or less than `1`, abort!
                \State::set([
                    'has' => [
                        'next' => false,
                        'parent' => false,
                        'prev' => false
                    ],
                    'is' => ['error' => 404]
                ]);
                $GLOBALS['t'][] = $language->isError;
                $this->status(404);
                $this->content('404' . $path . '/' . ($i + 1));
            }
            \State::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ]);
            $this->content('pages' . $path . '/' . ($i + 1));
        }
    }
}

\Route::over('*', __NAMESPACE__ . "\\route", 20);