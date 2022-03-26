<?php

Hook::set('_', function($_) use($user) {
    if (0 === \strpos($_['type'] . '/', 'page/') && (
        'set' === $_['task'] && substr_count($_['path'], '/') > 0 ||
        'get' === $_['task'] && substr_count($_['path'], '/') > 1
    )) {
        // Convert list of tag(s) slug into list of tag(s) ID
        Hook::set([
            'do.page.get',
            'do.page.set'
        ], function($_) use($user) {
            // Method not allowed!
            if ('POST' !== $_SERVER['REQUEST_METHOD']) {
                return $_;
            }
            // Abort by previous hook’s return value if any
            if (!empty($_['alert']['error'])) {
                return $_;
            }
            // Abort if current page is not a file
            if (!is_file($file = $_['file'] ?? P)) {
                return $_;
            }
            // Delete `kind.data` file if `data[kind]` field is empty
            $data = dirname($file) . D . pathinfo($file, PATHINFO_FILENAME) . D . 'kind.data';
            if (empty($_POST['tags']) && is_file($data)) {
                unlink($data);
                return $_;
            }
            if (!is_dir($d = LOT . D . 'tag')) {
                mkdir($d, 0775, true);
            }
            $any = map(Tags::from(LOT . D . 'tag', 'archive,page')->sort([-1, 'id']), static function($tag) {
                return $tag->id;
            })[0] ?? 0; // Get the highest tag ID
            $out = [];
            ++$any; // New ID must be unique
            foreach (preg_split('/\s*,+\s*/', $_POST['tags']) as $v) {
                if ("" === $v) {
                    continue;
                }
                if ($id = From::tag($v = To::kebab($v))) {
                    $out[] = $id;
                } else {
                    $out[] = $any;
                    if (!is_dir($dd = $d . D . $v)) {
                        mkdir($dd, 0775, true);
                    }
                    file_put_contents($f = $dd . '.page', To::page([
                        'title' => To::title($v),
                        'author' => $user->user ?? null
                    ]));
                    chmod($f, 0600);
                    file_put_contents($ff = $dd . D . 'id.data', $any);
                    chmod($ff, 0600);
                    file_put_contents($ff = $dd . D . 'time.data', date('Y-m-d H:i:s'));
                    chmod($ff, 0600);
                    $_['alert']['info'][$f] = ['%s %s successfully created.', ['Tag', '<code>' . x\panel\from\path($f) . '</code>']];
                    ++$any;
                }
            }
            if ($out) {
                sort($out);
                file_put_contents($data, json_encode($out));
                chmod($data, 0600);
            }
            return $_;
        }, 11);
        $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['tags'] = [
            'name' => 'tags',
            'stack' => 41,
            'state' => ['max' => 12],
            'type' => 'query',
            'value' => (new Page($_['file']))->query,
            'width' => true
        ];
    }
    return $_;
}, 0);