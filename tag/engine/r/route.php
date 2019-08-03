<?php namespace _\lot\x\tag;

$GLOBALS['tag'] = new \Tag;

function route($form) {
    global $config, $language, $url;
    $path = $this[0];
    $i = ($url->i ?? 1) - 1;
    // Load default page state(s)…
    $state = \state('tag');
    $sort = $config->tag('sort') ?? $config->page('sort') ?? [1, 'path'];
    $chunk = $config->tag('chunk') ?? $config->page('chunk') ?? 5;
    $chops = \explode('/', $path);
    $t = \array_pop($chops); // The tag name
    $p = \array_pop($chops); // The tag path
    // Get tag ID from tag name…
    if (null !== ($id = \From::tag($t))) {
        $GLOBALS['t'][] = $language->tag;
        if ($p === $state['/']) {
            $config->sort = $sort;
            $config->chunk = $chunk;
            $path = \implode('/', $chops);
            $r = PAGE . DS . $path;
            if ($file = \File::exist([
                $r . '.page',
                $r . '.archive'
            ])) {
                $page = new \Page($file);
            }
            $pages = \Get::pages($r, 'page')->sort($sort);
            if ($pages->count() > 0) {
                $pages = $pages->is(function($v) use($id) {
                    if (\is_file($k = \Path::F($v) . DS . 'kind.data')) {
                        $k = \e(\file_get_contents($k));
                    } else if (!$k = (\From::page(\file_get_contents($v))['kind'] ?? null)) {
                        return false;
                    }
                    $k = ',' . \implode(',', (array) $k) . ',';
                    return \strpos($k, ',' . $id . ',') !== false;
                });
            }
            if ($f = \File::exist([
                TAG . DS . $t . '.page',
                TAG . DS . $t . '.archive'
            ])) {
                $tag = new \Tag($f);
            }
            \Config::set([
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
            $path = '/' . $path . '/' . $p . '/' . $t;
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
                \Config::set([
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
            \Config::set('has', [
                'next' => !!$pager->next,
                'parent' => !!$pager->parent,
                'prev' => !!$pager->prev
            ]);
            $this->status(200);
            $this->content('pages' . $path . '/' . ($i + 1));
        }
    }
}

\Route::over('*', __NAMESPACE__ . "\\route", 20);