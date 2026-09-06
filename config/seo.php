<?php
/**
 * SEO integration bootstrap.
 *
 * @package Aegis\Plugin\Config
 * @since   1.0.0
 */

declare( strict_types=1 );

namespace Aegis\Plugin\Config;

use Aegis\Plugin\Blocks\VideoSchema;
use Aegis\Plugin\Seo\Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

( new Manager() )->init();

( new VideoSchema() )->init();
