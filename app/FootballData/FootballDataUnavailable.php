<?php

namespace App\FootballData;

use RuntimeException;

/**
 * football-data.org could not be reached, or refused the request.
 */
class FootballDataUnavailable extends RuntimeException {}
