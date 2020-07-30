<?php
define('PASTES_DIR', __DIR__.'/pastes');
if (!is_dir(PASTES_DIR) && !mkdir(PASTES_DIR)) {
    die("Failed to create the pastes dir!");
}
