<?php

// Store tag state to registry…
$state = Extend::state('tag');
if (!empty($state['tag'])) {
    Config::alt(['tag' => $state['tag']]);
}