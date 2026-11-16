<?php
/**
 * Unit tests for the colour row of a product page.
 *
 * @package CosyPaw\Tests
 */

declare(strict_types=1);

namespace Theme\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use Theme\Swatches;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/inc/Swatches.php';

final class SwatchesTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * One link per colour, the current one marked, each to its own page.
	 *
	 * @return void
	 */
	public function test_links_mark_the_current_colour(): void {
		$html = Swatches::links(
			array(
				array(
					'key'       => 'kruzic-braon',
					'color'     => 'Braon',
					'swatch'    => '#b08a62',
					'permalink' => 'https://x.test/braon/',
				),
				array(
					'key'       => 'kruzic-sivi',
					'color'     => 'Siva',
					'swatch'    => '#9b9a97',
					'permalink' => 'https://x.test/sivi/',
				),
			),
			'kruzic-sivi'
		);

		$this->assertStringContainsString( 'Izaberi boju', $html );
		$this->assertSame( 2, substr_count( $html, 'class="colour-swatch"' ) );
		$this->assertStringContainsString( 'href="https://x.test/braon/"', $html );
		$this->assertStringContainsString( 'href="https://x.test/sivi/" title="Siva" aria-label="Siva" aria-current="true"', $html );
		$this->assertSame( 1, substr_count( $html, 'aria-current' ) );
		$this->assertStringContainsString( '--swatch: #b08a62', $html );
	}

	/**
	 * A colour on its own has nothing to choose between.
	 *
	 * @return void
	 */
	public function test_a_single_colour_prints_nothing(): void {
		$one = array(
			array(
				'key'       => 'a',
				'color'     => 'Braon',
				'swatch'    => '#000',
				'permalink' => '/',
			),
		);

		$this->assertSame( '', Swatches::links( $one, 'a' ) );
		$this->assertSame( '', Swatches::links( array(), 'a' ) );
	}

	/**
	 * Radios that drive a select: the selected one checked, the select named.
	 *
	 * @return void
	 */
	public function test_radios_drive_the_select(): void {
		$html = Swatches::radios(
			array(
				array(
					'value'  => 'Plava',
					'label'  => 'Plava',
					'swatch' => '#5f86c9',
				),
				array(
					'value'  => 'Tamno plava',
					'label'  => 'Tamno plava',
					'swatch' => '#35507f',
				),
			),
			'Tamno plava',
			'boja'
		);

		$this->assertStringContainsString( 'data-colour-swatches="boja"', $html );
		$this->assertSame( 2, substr_count( $html, 'type="radio"' ) );
		$this->assertSame( 1, substr_count( $html, ' checked' ) );
		$this->assertStringContainsString( 'value="Tamno plava" checked', $html );
		$this->assertStringContainsString( 'name="boja-swatch"', $html );
	}
}
