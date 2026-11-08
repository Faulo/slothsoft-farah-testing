<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting;

use Socket;

// Loaded only by a test running in a separate process.
function socket_getsockname(Socket $socket, string &$address, ?int &$port = null): bool {
    unset($socket);
    $address = '';
    $port = 0;
    return false;
}
