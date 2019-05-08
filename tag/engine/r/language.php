<?php

Language::set([
    'tag' => ['Tag', 'Tag', 'Tags'],
    'tag-count' => function(int $i) {
        return $i . ' Tag' . ($i === 1 ? "" : 's');
    }
]);