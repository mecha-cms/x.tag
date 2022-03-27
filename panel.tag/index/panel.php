<?php

// Disable this extension if `tag` extension is disabled or removed ;)
if (!isset($state->x->tag)) {
    return $_;
}

Hook::set('_', function($_) use($user) {
    if ('POST' === $_SERVER['REQUEST_METHOD'] && 0 === strpos($_['path'] . '/', 'tag/') && ($_['file'] || $_['folder'])) {
        $current = $_POST['data']['id'] ?? $_POST['page']['id'] ?? 0;
        foreach (g('set' === $_['task'] ? $_['folder'] : dirname($_['file']), 'archive,page') as $k => $v) {
            $v = From::tag(pathinfo($k, PATHINFO_FILENAME)) ?? 0;
            if ($v === $current && $k !== $_['file']) {
                // Found duplicate tag ID!
                $_['alert']['error'][$k] = ['%s %s is in use.', ['ID', '<a href="' . x\panel\to\link([
                    'part' => 0,
                    'path' => 'tag/' . basename($k),
                    'query' => [
                        'tab' => ['data'],
                        'type' => null
                    ],
                    'task' => 'get'
                ]) . '"><code>' . $v . '</code></a>']];
                break;
            }
        }
        return $_;
    }
    if (0 === strpos($_['type'] . '/', 'page/') && (
        'set' === $_['task'] && substr_count($_['path'], '/') > 0 ||
        'get' === $_['task'] && substr_count($_['path'], '/') > 1
    )) {
        // Convert list of tag(s) slug into list of tag(s) ID
        Hook::set([
            'do.page.get',
            'do.page.set',
            'do.page/page.get',
            'do.page/page.set'
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
            // Delete `kind.data` file if `tags` field is empty
            $data = dirname($file) . D . pathinfo($file, PATHINFO_FILENAME) . D . 'kind.data';
            if (empty($_POST['tags'])) {
                is_file($data) && unlink($data);
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
    if (isset($_['lot']['bar']['lot'][0]['lot']['folder']['lot']['tag'])) {
        $_['lot']['bar']['lot'][0]['lot']['folder']['lot']['tag']['icon'] = 'M5.5,9A1.5,1.5 0 0,0 7,7.5A1.5,1.5 0 0,0 5.5,6A1.5,1.5 0 0,0 4,7.5A1.5,1.5 0 0,0 5.5,9M17.41,11.58C17.77,11.94 18,12.44 18,13C18,13.55 17.78,14.05 17.41,14.41L12.41,19.41C12.05,19.77 11.55,20 11,20C10.45,20 9.95,19.78 9.58,19.41L2.59,12.42C2.22,12.05 2,11.55 2,11V6C2,4.89 2.89,4 4,4H9C9.55,4 10.05,4.22 10.41,4.58L17.41,11.58M13.54,5.71L14.54,4.71L21.41,11.58C21.78,11.94 22,12.45 22,13C22,13.55 21.78,14.05 21.42,14.41L16.04,19.79L15.04,18.79L20.75,13L13.54,5.71Z';
    }
    return $_;
}, 0);

if (0 !== strpos($_['path'] . '/', 'tag/')) {
    return $_;
}

if (!array_key_exists('type', $_GET) && !isset($_['type'])) {
    if (!empty($_['part']) && $_['folder']) {
        $_['type'] = 'pages/tag';
    } else if (empty($_['part']) && $_['file']) {
        $x = pathinfo($_['file'], PATHINFO_EXTENSION);
        if ('data' === $x) {
            $_['type'] = 'data';
        } else if (in_array($x, ['archive', 'draft', 'page'])) {
            $_['type'] = 'page/tag';
        }
    }
}

if (0 === strpos($_['type'] . '/', 'pages/tag/')) {
    Hook::set('_', function($_) {
        if (
            !empty($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['pages']['lot']['pages']['lot']) &&
            !empty($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['pages']['lot']['pages']['type']) &&
            'pages' === $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['pages']['lot']['pages']['type']
        ) {
            foreach ($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['pages']['lot']['pages']['lot'] as $k => &$v) {
                $v['link'] = $v['url'] = false;
                // Disable page children feature
                $v['tasks']['enter']['skip'] = true;
                $v['tasks']['set']['skip'] = true;
            }
        }
        $_['lot']['desk']['lot']['form']['lot'][0]['lot']['tasks']['lot']['page']['description'][1] = 'Tag';
        $_['lot']['desk']['lot']['form']['lot'][0]['lot']['tasks']['lot']['page']['title'] = 'Tag';
        $_['lot']['desk']['lot']['form']['lot'][0]['lot']['tasks']['lot']['page']['url'] = [
            'part' => 0,
            'query' => [
                'chunk' => null,
                'deep' => null,
                'query' => null,
                'stack' => null,
                'tab' => null,
                'type' => 'page/tag',
                'x' => null
            ],
            'task' => 'set'
        ];
        return $_;
    }, 10.1);
} else if (0 === strpos($_['type'] . '/', 'page/tag/')) {
    $id = 0;
    if (empty($page) || is_object($page) && !$page->exist) {
        $page = new Tag($_['file']);
    }
    if ('set' === $_['task']) {
        foreach (g($_['folder'], 'archive,page') as $k => $v) {
            $v = From::tag(pathinfo($k, PATHINFO_FILENAME)) ?? 0;
            if ($v > $id) {
                $id = $v;
            }
        }
        ++$id;
    }
    Hook::set('_', function($_) use($id, $page) {
        $_['lot'] = array_replace_recursive($_['lot'] ?? [], [
            'bar' => [
                // `bar`
                'lot' => [
                    // `links`
                    0 => [
                        'lot' => [
                            'set' => [
                                'description' => ['New %s', 'Tag'],
                                'icon' => 'M21.41,11.58L12.41,2.58C12.04,2.21 11.53,2 11,2H4A2,2 0 0,0 2,4V11C2,11.53 2.21,12.04 2.59,12.41L3,12.81C3.9,12.27 4.94,12 6,12A6,6 0 0,1 12,18C12,19.06 11.72,20.09 11.18,21L11.58,21.4C11.95,21.78 12.47,22 13,22C13.53,22 14.04,21.79 14.41,21.41L21.41,14.41C21.79,14.04 22,13.53 22,13C22,12.47 21.79,11.96 21.41,11.58M5.5,7A1.5,1.5 0 0,1 4,5.5A1.5,1.5 0 0,1 5.5,4A1.5,1.5 0 0,1 7,5.5A1.5,1.5 0 0,1 5.5,7M10,19H7V22H5V19H2V17H5V14H7V17H10V19Z',
                                'url' => [
                                    'part' => 0,
                                    'path' => dirname($_['path']),
                                    'query' => [
                                        'chunk' => null,
                                        'deep' => null,
                                        'query' => null,
                                        'stack' => null,
                                        'tab' => null,
                                        'type' => 'page/tag',
                                        'x' => null
                                    ],
                                    'task' => 'set'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'desk' => [
                // `desk`
                'lot' => [
                    'form' => [
                        // `form/post`
                        'lot' => [
                            1 => [
                                // `section`
                                'lot' => [
                                    'tabs' => [
                                        // `tabs`
                                        'lot' => [
                                            'page' => [
                                                'lot' => [
                                                    'fields' => [
                                                        // `fields`
                                                        'lot' => [
                                                            'content' => ['skip' => true]
                                                        ]
                                                    ]
                                                ],
                                                'stack' => 10,
                                                'value' => 'tag'
                                            ],
                                            'data' => [
                                                'lot' => [
                                                    'fields' => [
                                                        'lot' => [
                                                            'id' => [
                                                                'fix' => true,
                                                                'name' => 'data[id]',
                                                                'stack' => 10,
                                                                'title' => 'ID',
                                                                'type' => 'number',
                                                                'value' => 'set' === $_['task'] ? $id : $page->id
                                                            ],
                                                            'link' => ['skip' => true]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        return $_;
    }, 10.1);
}

return $_;