<?php

Hook::set('__tag.url', function($content, $lot) use($__state) {
    $s = Path::F($lot['path'], LOT);
    return rtrim(__url__('url') . '/' . $__state->path . '/::g::/' . ltrim(To::url($s), '/'), '/');
});

$__folder = LOT . DS . $__path;
$__file = File::exist([
    $__folder . '.draft',
    $__folder . '.page',
    $__folder . '.archive'
], $__folder);

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
        Lot::set('__pages', $__pages);
    }
} else {
    if ($__sgr === 's') {
        
    } else if ($__sgr === 'g') {
        if ($__is_post) {
            $headers = [
                'title' => false,
                'description' => false,
                'author' => false,
                'type' => false,
                'content' => false
            ];
            foreach ($headers as $k => $v) {
                $headers[$k] = Request::post($k, $v);
            }
            $s = Path::N($__file);
            $ss = Request::post('slug');
            $x = Path::X($__file);
            $xx = Request::post('x', $x);
            $d = Path::D($__file);
            $dd = $d . DS . $ss;
            $ddd = $dd . '.' . $xx;
            if ($s !== $ss && File::exist([
                $dd . '.draft',
                $dd . '.page',
                $dd . '.archive'
            ])) {
                Request::save('post');
                Message::error('exist', [$language->slug, '<em>' . $ss . '</em>']);
            }
            $f = Path::D($__file) . DS . $ss . '.' . $xx;
            Hook::fire('on.page.set', [$f]);
            if (!Message::$x) {
                Page::open($__file)->data($headers)->save(0600);
                if ($s !== $ss || $x !== $xx) {
                    // Rename folder…
                    if ($s !== $ss) {
                        File::open(Path::F($__file))->renameTo($ss);
                    }
                    // Rename file…
                    File::open($__file)->renameTo($ss . '.' . $xx);
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
                Message::success(To::sentence($language->updateed));
                Guardian::kick(Path::D($url->current) . '/' . $ss);
            }
        }
        if ($__file = File::exist([
            $__folder . '.draft',
            $__folder . '.page',
            $__folder . '.archive'
        ])) {
            $__page = [
                new Page($__file, [], '__tag'),
                new Page($__file, [], 'tag')
            ];
            Lot::set('__page', $__page);
        }
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
}