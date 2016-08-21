<?php

use Myxobek\RemoteImagesLoader\RemoteImagesLoader;

class RemoteImagesLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testCheckPackageRequirements()
    {
        $remoteImagesLoader = new RemoteImagesLoader([
            'host'          => '',
            'login'         => '',
            'password'      => '',
            'extensions'    => ['jpg', 'png', 'gif'],
            'destination'   => ''
        ]);
    }
}