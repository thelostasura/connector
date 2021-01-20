<?php

namespace TheLostAsura\Connector;

use TheLostAsura\Connector\Ajax\Admin;
use TheLostAsura\Connector\Ajax\Frontend;
use TheLostAsura\Connector\Utils\Access;

class Ajax
{
    public function __construct() {
        if (Access::can(true)) {
            new Admin();
            new Frontend();
        }
    }
}
