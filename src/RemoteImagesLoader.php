<?php

namespace Myxobek\RemoteImagesLoader;

class RemoteImagesLoader
{
    ///////////////////////////////////////////////////////////////////////////
    
    const ERROR_CODE_OK                         = 0;
    const ERROR_CODE_UNKNOWN_HOST               = 1;
    const ERROR_CODE_CANNOT_LOGIN               = 2;
    const ERROR_CODE_CANNOT_SET_PASSIVE_MODE    = 3;
    
    const DEFAULT_DESTINATION = './images/';
    const DEFAULT_EXTENSIONS  = ['jpg', 'png', 'gif'];
    
    ///////////////////////////////////////////////////////////////////////////
    
    private $host           = '';
    private $login          = '';
    private $password       = '';
    
    private $extensions     = self::DEFAULT_EXTENSIONS;
    
    private $destination    = self::DEFAULT_DESTINATION;
    
    public $connection     = false;
    
    public $error_code     = self::ERROR_CODE_OK;
    public $error_last_msg = '';
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * Initializes package
     *
     * @author  myxobek
     *
     * @param   array   $data
     */
    public function init( $data )
    {
        $this->host     = isset( $data['host'] )        ? $data['host']     : '';
        $this->login    = isset( $data['login'] )       ? $data['login']    : '';
        $this->password = isset( $data['password'] )    ? $data['password'] : '';
        
        if ( isset( $data['extensions'] ) && !empty( $data['extensions'] ) )
        {
            $this->extensions = $data['extensions'];
        }
    
        if ( isset( $data['destination'] ) && strlen( $data['destination'] ) > 0 )
        {
            $this->destination = $data['destination'];
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * RemoteImagesLoader destructor
     *
     * @author  myxobek
     */
    public function __destruct()
    {
        $this->host         = '';
        $this->login        = '';
        $this->password     = '';
    
        $this->extensions   = self::DEFAULT_EXTENSIONS;
    
        $this->destination  = self::DEFAULT_DESTINATION;
    
        $this->connection   = false;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * getErrorText
     *
     * @author  myxobek
     *
     * @param   int $error_code
     *
     * @return  string
     */
    public function getErrorText( $error_code )
    {
        switch ( $error_code )
        {
            case( self::ERROR_CODE_UNKNOWN_HOST ):
                $result = 'Couldn\'t set passive mode';
                break;
            case( self::ERROR_CODE_CANNOT_LOGIN ):
                $result = 'Couldn\'t set passive mode';
                break;
            case( self::ERROR_CODE_CANNOT_SET_PASSIVE_MODE ):
                $result = 'Couldn\'t set passive mode';
                break;
            case( self::ERROR_CODE_OK ):
                $result = 'Everything is fine';
                break;
            default:
                $result = 'Unknowm error';
                break;
        }
        
        return $result;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * checkPackageRequirements
     *
     * Checks variables to be correct for correct work of this package
     *
     * @author      myxobek
     *
     * @return      bool
     */
    public function checkPackageRequirements()
    {
        $this->error_code = $this->establishConnection();
        
        if ( $this->error_code !== self::ERROR_CODE_OK )
        {
            $common_error_message = 'Couldn\'t properly initialize package. Have a look at response below for more information:';
        
            $error_message = $this->getErrorText( $this->error_code );
        
            $this->error_last_msg = (
            implode('',
                [
                    $common_error_message,
                    "\n",
                    $error_message
                ])
            );
            
            return false;
        }
        else
        {
            $this->error_last_msg = $this->getErrorText( $this->error_code );
            
            return true;
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * establishConnection
     *
     * @author      myxobek
     *
     * @return      int
     */
    public function establishConnection()
    {
        $this->connection = @ftp_connect( $this->host );
        
        // create connection
        if ( $this->connection !== false )
        {
            // try to login
            if ( $login_result = @ftp_login( $this->connection, $this->login, $this->password ) )
            {
                // set passive mode
                if ( ftp_pasv( $this->connection, true ) !== false )
                {
                    $error_code = self::ERROR_CODE_OK;
                }
                else
                {
                    $error_code = self::ERROR_CODE_CANNOT_SET_PASSIVE_MODE;
                }
            }
            else
            {
                $error_code = self::ERROR_CODE_CANNOT_LOGIN;
            }
        }
        else
        {
            $error_code = self::ERROR_CODE_UNKNOWN_HOST;
        }
        
        return $error_code;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * getImages
     *
     * @author      myxobek
     *
     * @return      array|boolean
     */
    public function getImagesList()
    {
        $error_code = $this->establishConnection();
        
        if ( $error_code === self::ERROR_CODE_OK )
        {
            $list = ftp_rawlist( $this->connection, '.', true );
    
            $images = [];
            $key    = '';
    
            // get files from root directory
            $i = 0;
            while( $list[$i] !== '' )
            {
                $file = $this->_getFIlename( $list[$i] );
        
                if ( $this->_isImage( $file ) )
                {
                    $images[$key][] = $file;
                }
        
                ++$i;
            }
    
            // get images from subdirectories
            /**
             * Get images from subdirectories
             *
             * Have a look at http://php.net/manual/ru/function.ftp-rawlist.php to see how ftp_rawlist returns list
             */
            for( $n = count( $list ); $i < $n; ++$i )
            {
                if ( $list[$i] == '' )
                {
                    $key = $this->_getDirectoryName( $list[$i+1] );
                    ++$i;
                }
        
                $j = $i+1;
                while (
                    ( $j < $n ) &&
                    ( $list[$j] != '' )
                )
                {
                    $file = $this->_getFIlename( $list[$j] );
                    if ( $this->_isImage( $file ) )
                    {
                        $images[$key][] = $file;
                    }
            
                    ++$j;
                }
        
                // $i will become $i+1 on next iteration
                $i = $j-1;
            }
            
            return $images;
        }
        else
        {
            return false;
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * downloadImages
     *
     * @author      myxobek
     *
     * @return      bool
     */
    public function downloadImages()
    {
        $images_list = $this->getImagesList();
        
        if ( $images_list !== false )
        {
            $this->createFolders( array_keys( $images_list ) );
    
            foreach ( $images_list as $directory => $images )
            {
                foreach ( $images as $image )
                {
                    $this->loadImage(
                        implode(
                            '',
                            [
                                $this->destination,
                                $directory,
                                '/',
                                $image
                            ]
                        ),
                        implode(
                            '',
                            [
                                $directory,
                                '/',
                                $image
                            ]
                        )
                    );
                }
            }
            
            return true;
        }
        else
        {
            return false;
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * loadImage
     *
     * @author      myxobek
     *
     * @param   string  $local_file
     * @param   string  $remote_file
     *
     * @return  bool
     */
    public function loadImage( $local_file, $remote_file )
    {
        if ( ftp_get( $this->connection, $local_file, $remote_file, FTP_BINARY) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * createFolders
     *
     * @author      myxobek
     *
     * @param   array   $keys
     */
    public function createFolders( $keys )
    {
        foreach ( $keys as $directory )
        {
            exec( 'mkdir -p ' . $this->destination . $directory, $output, $return_var );
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * _getFIlename
     *
     * Strings are like: drwxr-x---   7 user group     4096 Jun 23 22:49 .
     *
     * @author      myxobek
     *
     * @param   string  $string
     *
     * @return mixed
     */
    private function _getFIlename( $string )
    {
        $string_as_array = explode( ' ', $string );
        
        return $string_as_array[ count( $string_as_array ) -1 ];
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function _isImage( $filename )
    {
        if ( preg_match( '#^.*?\.(' . implode('|', $this->extensions) . ')$#', $filename ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    private function _getDirectoryName( $directory )
    {
        return substr( $directory, 0, -1);
    }
    
    ///////////////////////////////////////////////////////////////////////////
}


