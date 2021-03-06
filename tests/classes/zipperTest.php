<?php
namespace GalleryZip\Zipper;

require_once PLUGIN_BASE_PATH . '/classes/zipper.php';

/**
 * Test class for GalleryZip.
 * Generated by PHPUnit on 2013-05-16 at 10:06:41.
 * @covers GalleryZip\Zipper\Zipper
 */
class ZipperTest extends \WP_UnitTestCase
{
	/**
	 * @var Zipper
	 */
	protected $zipper;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	public function setUp() {
		$this->zipper = new Zipper( false );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	public function tearDown() {
		$zipper = $this->zipper;
		$cachedir = $zipper::$cache_dir;
		$this->_rmdir( $cachedir );

		$this->remove_test_dir();

	}

	private function _rmdir( $path ) {
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->rmdir( $path, true );

	}

	/**
	 * create a test directory
	 * @return string
	 */
	private function create_test_dir() {
		$testdir = WP_CONTENT_DIR . '/testdir/';

		if ( ! is_dir( $testdir ) )
			mkdir( $testdir );

		return $testdir;
	}

	/**
	 * removes the test directory
	 * @return boolean
	 */
	private function remove_test_dir() {
		$testdir = WP_CONTENT_DIR . '/testdir/';

		if ( ! is_dir( $testdir ) )
			return true;

		$files_inside = glob( $testdir . '*' );
		if ( ! empty( $files_inside ) )
			array_walk( $files_inside, function($file){unlink($file);} );

		rmdir( $testdir );
	}

	/**
	 * fill the test directory with a random number of files
	 * @param unknown $testdir
	 */
	private function fill_test_dir( $testdir ) {
		$testdir   = rtrim( $testdir, '/' ) . '/';
		$testfile  = 'testfile';
		$num_files = rand( 5, 10 );

		for ( $i = 0; $i < $num_files; $i++ ) {
			file_put_contents( $testdir.$testfile.$i.'.txt', str_repeat( 'FooBarBaz', rand( 10, 50 ) ) );
		}
	}

	/**
	 * @covers GalleryZip\Zipper\Zipper::__construct()
	 */
	public function testConstruct() {
		$zipper = $this->zipper;
		$this->assertTrue( $zipper::$cache_dir_exists );
	}

	/**
	 * @covers GalleryZip\Zipper\Zipper::create_cache_dir()
	 */
	public function testCreate_cache_dir() {
		$zipper = $this->zipper;
		$cachedir = $zipper::$cache_dir;
		$actual = $this->zipper->create_cache_dir();
		$this->assertEquals( $cachedir, $actual );
	}

	/**
	 * @covers GalleryZip\Zipper\Zipper::to_url()
	 */
	public function testTo_url() {
		$test     = WP_CONTENT_DIR . 'test/file.zip';
		$expected = WP_CONTENT_URL . '/test/file.zip';
		$actual   = $this->zipper->to_url( $test );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * @covers GalleryZip\Zipper\Zipper::zip_files()
	 */
	public function testZip_files() {
		$zipper   = $this->zipper;
		$cachedir = $zipper::$cache_dir;

		$testdir = $this->create_test_dir();
		$this->fill_test_dir( $testdir );

		$target    = $cachedir . 'test.zip';
		$file_list = glob( $testdir . '*' );

		$condition = $this->zipper->zip_files( $target, $file_list );

		$this->assertTrue( $condition );
		$this->assertFileExists( $target );
	}

	public function testZipFiles_Force_PclZip() {
		$zipper   = $this->zipper;
		$cachedir = $zipper::$cache_dir;

		$testdir = $this->create_test_dir();
		$this->fill_test_dir( $testdir );

		$target    = $cachedir . 'test.zip';
		$file_list = glob( $testdir . '*' );

		// forcing to use PclZip
		$zipper::$pclzip = true;
		$condition = $this->zipper->zip_files( $target, $file_list );

		$this->assertTrue( $condition );
		$this->assertFileExists( $target );
	}

	/**
	 * @covers GalleryZip\Zipper\Zipper::zip_images()
	 */
	public function testZip_images() {
		$zipper   = $this->zipper;
		$cachedir = $zipper::$cache_dir;

		$testdir = $this->create_test_dir();
		$this->fill_test_dir( $testdir );

		$target    = 'test.zip';
		$file_list = glob( $testdir . '*' );

		$zipfile = $this->zipper->zip_images( $target, $file_list );

		$beginns_with_http = ( 0 == strpos( $zipfile, 'http' ) );

		$this->assertTrue( $beginns_with_http );

	}
}
