<?php

function fn_tag_set($__path) {
    if (!Message::$x) {
        global $language;
        // Create `kind.data` file…
        if ($s = Request::post('query')) {
            $s = explode(',', $s);
            $__kinds = [];
            if (count($s) > 12) {
                Message::error('max', [$language->tags, '<code>12</code>']);
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
                        File::write($__o)->saveTo(TAG . DS . $v . DS . 'id.data', 0600);
                        Page::data(['title' => $v])->saveTo(TAG . DS . $v . '.page', 0600);
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

Hook::set('on.page.set', 'fn_tag_set');