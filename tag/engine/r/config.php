<?php

// Store tag state to registry…
$state = state('tag');
if (!empty($state['tag'])) {
    // Prioritize default state
    Config::over($state);
}