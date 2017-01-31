<?php

if ($__chops[0] === 'page' && substr($__path, -2) !== '/+' && strpos($__path, '/+/') === false) {

    function fn_tags_set($__path) {
        if (!Message::$x) {
            global $language;
            // Create `kind.data` file…
            if ($s = Request::post('tags')) {
                $s = explode(',', $s);
                $__kinds = [];
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
                            $f = TAG . DS . $v . DS;
                            File::write(date(DATE_WISE))->saveTo($f . 'time.data', 0600);
                            File::write($__o)->saveTo($f . 'id.data', 0600);
                            Page::data(['title' => $v])->saveTo($f . '.page', 0600);
                            Message::info('create', $language->tag . ' <em>' . str_replace('-', ' ', $v) . '</em>');
                        }
                    }
                    $__kinds = array_unique($__kinds);
                    sort($__kinds);
                    File::write(To::json($__kinds))->saveTo(Path::F($__path) . DS . 'kind.data', 0600);
                }
            } else {
                File::open(Path::F($__path) . DS . 'kind.data')->delete();
            }
        }
    }

    Hook::set('on.page.set', 'fn_tags_set');

    function panel_f_tags($__lot) {
        extract($__lot);
        echo '<p class="f">';
        echo '<label for="f-tags">' . $language->tags . '</label>';
        echo ' <span>';
        $__tags = [];
        foreach ($__page[1]->tags as $v) {
            $__tags[] = str_replace('-', ' ', $v->slug);
        }
        echo Form::text('tags', implode(', ', (array) $__tags), $language->f_query, [
            'classes' => ['input', 'block', 'query'],
            'id' => 'f-tags'
        ]);
        echo '</span>';
        echo '</p>';
        return $__lot;
    }

    Hook::set('panel.m.editor', 'panel_f_tags', 60.1);

}