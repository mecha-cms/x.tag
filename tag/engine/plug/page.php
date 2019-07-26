<?php namespace _\lot\x\tag;

function query() {
    $query = [];
    foreach ((array) $this['kind'] as $v) {
        if ($slug = \To::tag($v)) {
            $query[] = \strtr($slug, '-', ' ');
        }
    }
    return $query;
}

function tags() {
    $tags = [];
    foreach ($this['query'] as $v) {
        $v = \strtr($v, ' ', '-');
        $tags[] = TAG . DS . $v . '.page';
    }
    return new \Tags($tags);
}

\Page::_('query', __NAMESPACE__ . "\\query");
\Page::_('tags', __NAMESPACE__ . "\\tags");