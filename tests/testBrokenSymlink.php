<?php

/**
 * Tests for the Archive process with symlinks
 *
 * @extends WP_UnitTestCase
 */
class testBrokenSymlinkTestCase extends WP_UnitTestCase {

	/**
	 * Contains the current backup instance
	 *
	 * @var object
	 * @access protected
	 */
	protected $backup;

	/**
	 * Setup the backup object and create the tmp directory
	 *
	 * @access public
	 * @return null
	 */
	function setUp() {
	
		if ( ! function_exists( 'symlink' ) )
			$this->markTestSkipped( 'symlink function not defined' );

		$this->backup = new HM_Backup();
		$this->backup->root = dirname( __FILE__ ) . '/test-data/';
		$this->backup->path = dirname( __FILE__ ) . '/tmp';
		$this->backup->files_only = true;

		mkdir( $this->backup->path() );

		$this->symlink = dirname( __FILE__ ) . '/test-data/' . basename( __FILE__ );

		file_put_contents( dirname( __FILE__ ) . '/test-data/symlink', '' );

		symlink( dirname( __FILE__ ) . '/test-data/symlink', $this->symlink );

		unlink( dirname( __FILE__ ) . '/test-data/symlink' );
		
		remove_action( 'hmbkp_backup_started', 'hmbkp_set_status', 10, 0 );
		remove_action( 'hmbkp_mysqldump_started', 'hmbkp_set_status_dumping_database' );
		remove_action( 'hmbkp_archive_started', 'hmbkp_set_status_archiving' );

	}

	/**
	 * Cleanup the backup file and tmp directory
	 * after every test
	 *
	 * @access public
	 * @return null
	 */
	function tearDown() {
	
		if ( ! function_exists( 'symlink' ) )
			return;
		
		if ( file_exists( $this->backup->archive_filepath() ) )
			unlink( $this->backup->archive_filepath() );
		
		if ( file_exists( $this->backup->path() ) )
			rmdir( $this->backup->path() );

		unlink( $this->symlink );

	}

	/**
	 * Test an unreadable file with the shell commands
	 *
	 * @access public
	 * @return null
	 */
	function testArchiveBrokenSymlinkWithZip() {

		if ( ! $this->backup->zip_command_path )
            $this->markTestSkipped( "Empty zip command path" );

		$this->assertFileNotExists( $this->symlink );
		$this->assertTrue( is_link( $this->symlink ) );

		$this->backup->zip();

		$this->assertFileExists( $this->backup->archive_filepath() );

		$this->assertArchiveNotContains( $this->backup->archive_filepath(), array( basename( $this->symlink ) ) );
		$this->assertArchiveFileCount( $this->backup->archive_filepath(), 3 );

		$this->assertEmpty( $this->backup->errors() );

	}

	/**
	 * Test an unreadable file with the zipArchive commands
	 *
	 * @access public
	 * @return null
	 */
	function testArchiveBrokenSymlinkWithZipArchive() {

		$this->backup->zip_command_path = false;

		$this->assertFileNotExists( $this->symlink );
		$this->assertTrue( is_link( $this->symlink ) );

		$this->backup->zip_archive();

		$this->assertFileExists( $this->backup->archive_filepath() );

		$this->assertArchiveNotContains( $this->backup->archive_filepath(), array( basename( $this->symlink ) ) );
		$this->assertArchiveFileCount( $this->backup->archive_filepath(), 3 );

		$this->assertEmpty( $this->backup->errors() );

	}

	/**
	 * Test an unreadable file with the PclZip commands
	 *
	 * @access public
	 * @return null
	 */
	function testArchiveBrokenSymlinkWithPclZip() {

		$this->backup->zip_command_path = false;

		$this->assertFileNotExists( $this->symlink );
		$this->assertTrue( is_link( $this->symlink ) );

		$this->backup->pcl_zip();

		$this->assertFileExists( $this->backup->archive_filepath() );

		$this->assertArchiveNotContains( $this->backup->archive_filepath(), array( basename( $this->symlink ) ) );
		$this->assertArchiveFileCount( $this->backup->archive_filepath(), 3 );

		$this->assertEmpty( $this->backup->errors() );

	}

}