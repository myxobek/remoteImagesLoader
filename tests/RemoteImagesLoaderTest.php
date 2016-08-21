<?php

use Myxobek\RemoteImagesLoader\RemoteImagesLoader;

class RemoteImagesLoaderTest extends PHPUnit_Framework_TestCase
{
    private $data = [
        'host'      => '',
        'login'     => '',
        'password'  => ''
    ];
    
    ///////////////////////////////////////////////////////////////////////////
    
    private function _getConfData()
    {
        $handle = fopen('.conf', 'r');
        if ( $handle )
        {
            while( ( $line = fgets( $handle ) ) !== false)
            {
                list( $key, $value ) = explode( '=', $line );
    
                $this->data[$key] = substr( $value, 0, -1);
            }
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function setUp()
    {
        parent::setUp();
        
        $this->_getConfData();
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function testGetErrorText()
    {
        $remoteImagesLoader = new RemoteImagesLoader();
        
        $this->assertEquals($remoteImagesLoader->getErrorText( $remoteImagesLoader::ERROR_CODE_OK ), 'Everything is fine' );
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function testCheckPackageRequirements()
    {
        $remoteImagesLoader = new RemoteImagesLoader();
        
        $this->assertFalse( $remoteImagesLoader->checkPackageRequirements() );
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function testGetImagesList()
    {
        $remoteImagesLoader = new RemoteImagesLoader();
        
        $remoteImagesLoader->init([
            'host'      => $this->data['host'],
            'login'     => $this->data['login'],
            'password'  => $this->data['password'],
        ]);
        
        $this->assertTrue( is_array( $remoteImagesLoader->getImagesList() ) );
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function testDownloadImages()
    {
        $remoteImagesLoader = new RemoteImagesLoader();
    
        $remoteImagesLoader->init([
            'host'      => $this->data['host'],
            'login'     => $this->data['login'],
            'password'  => $this->data['password'],
        ]);
    
        $this->assertTrue( $remoteImagesLoader->downloadImages() );
    }
    
    ///////////////////////////////////////////////////////////////////////////
}