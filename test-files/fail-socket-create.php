<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting;

// Loaded only by a test running in a separate process.
function socket_create_listen(int $port): false {
    unset($port);
    return false;
}
