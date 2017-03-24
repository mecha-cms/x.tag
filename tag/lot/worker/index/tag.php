<?php

Hook::set('__tag.url', function($__content, $__lot) use($__state) {
    $__s = Path::F($__lot['path'], LOT);
    return rtrim(__url__('url') . '/' . $__state->path . '/::g::/' . ltrim(To::url($__s), '/'), '/');
});

if ($__is_pages) {
    $site->is = 'pages';
    if ($__files = Get::tags($__folder, 'draft,page', [1, 'id'], 'path')) {
        if ($__query = l(Request::get('q', ""))) {
            Message::info('search', '<em>' . $__query . '</em>');
            $__query = explode(' ', $__query);
            $__files = array_filter($__files, function($v) use($__query) {
                $v = Path::N($v);
                foreach ($__query as $__q) {
                    if (strpos($v, $__q) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }
        foreach ($__files as $v) {
            $__pages[0][] = new Page($v, [], '__tag');
            $__pages[1][] = new Page($v, [], 'tag');
        }
        Lot::set([
            '__pages' => $__pages,
            '__pager' => [new Elevator($__files ?: [], $__chunk, $__step, $url . '/' . $__state->path . '/::g::/tag', [
                'direction' => [
                   '-1' => 'previous',
                    '0' => false,
                    '1' => 'next'
                ],
                'union' => [
                   '-2' => [
                        2 => ['rel' => null, 'classes' => ['button', 'x']]
                    ],
                   '-1' => [
                        1 => '&#x276E;',
                        2 => ['rel' => 'prev', 'classes' => ['button']]
                    ],
                    '1' => [
                        1 => '&#x276F;',
                        2 => ['rel' => 'next', 'classes' => ['button']]
                    ]
                ]
            ], '__pages')]
        ]);
    }
} else {
    if ($__is_post) {
        $s = Path::N($__file);
        $ss = Request::post('slug');
        $x = Path::X($__file);
        $xx = Request::post('x', $x);
        $d = Path::D($__file);
        $dd = $d . DS . $ss;
        $ddd = $dd . '.' . $xx;
        $headers = [
            'title' => false,
            'description' => false,
            'author' => false,
            'type' => false,
            'content' => false
        ];
        foreach ($headers as $k => $v) {
            if (file_exists($dd . DS . $k . '.data')) continue;
            $headers[$k] = Request::post($k, $v);
        }
        if ($s !== $ss && File::exist([
            $dd . '.draft',
            $dd . '.page',
            $dd . '.archive'
        ])) {
            Request::save('post');
            Message::error('exist', [$language->slug, '<em>' . $ss . '</em>']);
        }
        $f = Path::D($__file) . DS . $ss . '.' . $xx;
        Hook::fire('on.tag.set', [$f]);
        if (!Message::$x) {
            if ($__sgr === 'g') {
                Page::open($__file)->data($headers)->save(0600);
                if ($s !== $ss || $x !== $xx) {
                    // Rename folder…
                    if ($s !== $ss) {
                        File::open(Path::F($__file))->renameTo($ss);
                    }
                    // Rename file…
                    File::open($__file)->renameTo($ss . '.' . $xx);
                }
            } else {
                if ($__sgr === 's') {
                    $dd = $__file . DS . $ss; // New tag…
                }
                Page::data($headers)->saveTo($__folder . DS . $ss . '.' . $xx, 0600);
            }
            // Create `time.data` file…
            if (!$s = Request::post('time')) {
                $s = date(DATE_WISE);
            } else {
                $s = DateTime::createFromFormat('Y/m/d H:i:s', $s)->format(DATE_WISE);
            }
            File::write($s)->saveTo($dd . DS . 'time.data', 0600);
            // Create `sort.data` file…
            if ($s = Request::post('sort')) {
                File::write(To::json($s))->saveTo($dd . DS . 'sort.data', 0600);
            }
            // Create `chunk.data` file…
            if ($s = Request::post('chunk')) {
                File::write($s)->saveTo($dd . DS . 'chunk.data', 0600);
            }
            if ($__sgr === 'g') {
                Message::success(To::sentence($language->updateed));
                Guardian::kick(Path::D($url->current) . '/' . $ss);
            } else {
                Message::success(To::sentence($language->createed));
                Guardian::kick(str_replace('::s::', '::g::', $url->current) . '/' . $ss);
            }
        }
    } else {
        if (($__file === $__folder || $__sgr === 's') && isset($__chops[1])) {
            Shield::abort(PANEL_404);
        }
    }
    if ($__file = File::exist([
        $__folder . '.draft',
        $__folder . '.page'
    ])) {
        $__page = [
            new Page($__file, [], '__tag'),
            new Page($__file, [], 'tag')
        ];
    } else {
        $__page = [
            new Page(null, [], '__tag'),
            new Page(null, [], 'tag')
        ];
    }
    Lot::set('__page', $__page);
    if ($__files = Get::tags(Path::D($__folder), 'draft,page', $__sort, 'path')) {
        $s = Path::N($__file);
        $__files = array_filter($__files, function($v) use($s) {
            return Path::N($v) !== $s;
        });
        foreach (Anemon::eat($__files)->chunk($__chunk, 0) as $k => $v) {
            $__kins[0][] = new Page($v, [], '__tag');
            $__kins[1][] = new Page($v, [], 'tag');
        }
        $__is_kin_has_step = count($__files) > $__chunk;
        Lot::set([
            '__kins' => $__kins,
            '__is_kin_has_step' => $__is_kin_has_step
        ]);
    }
}