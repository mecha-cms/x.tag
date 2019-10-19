<?php

$GLOBALS['_']['lot']['bar']['lot'][0]['lot']['folder']['lot']['tag']['icon'] = 'M5.5,9A1.5,1.5 0 0,0 7,7.5A1.5,1.5 0 0,0 5.5,6A1.5,1.5 0 0,0 4,7.5A1.5,1.5 0 0,0 5.5,9M17.41,11.58C17.77,11.94 18,12.44 18,13C18,13.55 17.78,14.05 17.41,14.41L12.41,19.41C12.05,19.77 11.55,20 11,20C10.45,20 9.95,19.78 9.58,19.41L2.59,12.42C2.22,12.05 2,11.55 2,11V6C2,4.89 2.89,4 4,4H9C9.55,4 10.05,4.22 10.41,4.58L17.41,11.58M13.54,5.71L14.54,4.71L21.41,11.58C21.78,11.94 22,12.45 22,13C22,13.55 21.78,14.05 21.42,14.41L16.04,19.79L15.04,18.79L20.75,13L13.54,5.71Z';

if ($_['content'] === 'page.page') {
    $GLOBALS['_']['form']['data']['kind'] = function($value) {
        $i = map(Tags::from(TAG, 'archive,page')->sort([-1, 'id']), function($tag) {
            return $tag->id;
        })[0] ?? 0; // Get the highest tag ID
        $out = [];
        ++$i; // New ID must be unique
        foreach (preg_split('/\s*,+\s*/', $value) as $v) {
            if (!$v) {
                continue;
            }
            if ($id = From::tag($v = To::kebab($v))) {
                $out[] = $id;
            } else {
                $out[] = $i;
                (new Tag($f = TAG . DS . $v . '.page'))->set([
                    'title' => To::title($v),
                    'author' => $GLOBALS['user']->user ?? null
                ])->save(0600);
                (new File(TAG . DS . $v . DS . 'id.data'))->set($i)->save(0600);
                (new File(TAG . DS . $v . DS . 'time.data'))->set(date('Y-m-d H:i:s'))->save(0600);
                $GLOBALS['_']['alert']['info'][] = ['%s %s successfully created.', ['Tag', _\lot\x\panel\h\path($f)]];
                ++$i;
            }
        }
        return $out;
    };
    $GLOBALS['_']['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['tags'] = [
        'type' => 'Query',
        'name' => 'data[kind]',
        'value' => (new Page($_['f']))->query,
        'width' => true,
        'stack' => 41
    ];
}
