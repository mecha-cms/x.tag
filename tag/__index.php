<?php

function fn_tags_set($__path) {
    if (!Message::$x) {
        global $language;
        // Create `kind.data` file…
        if ($s = Request::post('tags')) {
            $s = explode(',', $s);
            $__kinds = [];
            $__author = Request::post('author', false);
            if (count($s) > 12) {
                Request::save('post');
                Message::error('max', [$language->tags, '<strong>12</strong>']);
            } else {
                foreach ($s as $v) {
                    $v = To::slug($v);
                    if (($id = From::tag($v)) !== false) {
                        $__kinds[] = $id;
                    } else {
                        $__o = 0;
                        foreach (glob(TAG . DS . '*' . DS . 'id.data', GLOB_NOSORT) as $vv) {
                            $id = (int) file_get_contents($vv);
                            if ($id > $__o) $__o = $id;
                        }
                        ++$__o;
                        $__kinds[] = $__o;
                        $f = TAG . DS . $v;
                        Hook::fire('on.tag.set', [$__path, $__o]);
                        File::write(date(DATE_WISE))->saveTo($f . DS . 'time.data', 0600);
                        File::write($__o)->saveTo($f . DS . 'id.data', 0600);
                        Page::data([
                            'title' => $v,
                            'author' => $__author
                        ])->saveTo($f . '.page', 0600);
                        Message::info('create', $language->tag . ' <em>' . str_replace('-', ' ', $v) . '</em>');
                    }
                }
                $__kinds = array_unique($__kinds);
                sort($__kinds);
                Hook::fire('on.tags.set', [$__path, $__kinds]);
                if (!Message::$x) {
                    File::write(To::json($__kinds))->saveTo(Path::F($__path) . DS . 'kind.data', 0600);
                }
            }
        } else {
            Hook::fire('on.tags.reset', [$__path, []]);
            File::open(Path::F($__path) . DS . 'kind.data')->delete();
        }
    }
}

Hook::set('on.page.set', 'fn_tags_set');

function fn_tags_f($__content) {
    $__index = strpos($__content, '<p class="f f-link">');
    if ($__index === false) return $__content;
    extract(Lot::get(null, []));
    $__index += strpos(substr($__content, $__index), '</p>') + 4;
    $__html  = "";
    $__html .= '<p class="f f-tags">';
    $__html .= '<label for="f-tags">' . $language->tags . '</label>';
    $__html .= '<span>';

    $__tags = [];
    if ($__page[0]->kind) {
        foreach ($__page[0]->kind as $__v) {
            $__tags[] = To::tag($__v);
        }
    }

    sort($__tags);

    $__html .= Form::text('tags', implode(', ', (array) $__tags), $language->f_query, [
        'classes' => ['input', 'block', 'query'],
        'id' => 'f-tags'
    ]);
    $__html .= '</span>';
    $__html .= '</p>';
    return substr($__content, 0, $__index) . $__html . substr($__content, $__index);
}

if ($__chops[0] === 'page') Hook::set('shield.output', 'fn_tags_f');

// Delete trash…
Hook::set('on.user.exit', function() {
    foreach (File::explore(TAG, true, true) as $k => $v) {
        if ($v === 0) continue;
        $s = Path::F($k);
        foreach (g($s, 'trash') as $v) {
            File::open($v)->delete();
        }
        if (Path::X($k) === 'trash') {
            File::open($k)->delete();
            if (Is::D($s)) {
                File::open($s)->delete();
            }
        }
    }
});