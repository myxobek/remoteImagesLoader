<?php

namespace Myxobek\RemoteImagesLoader;

class RemoteImagesLoader
{
    ///////////////////////////////////////////////////////////////////////////
    
    const ERROR_CODE_OK                         = 0;
    const ERROR_CODE_UNKNOWN_HOST               = 1;
    const ERROR_CODE_CANNOT_LOGIN               = 2;
    const ERROR_CODE_CANNOT_SET_PASSIVE_MODE    = 3;
    
    ///////////////////////////////////////////////////////////////////////////
    
    private $host           = '';
    private $login          = '';
    private $password       = '';
    
    private $extensions     = ['jpg', 'png', 'gif'];
    
    private $destination    = './';
    
    private $connection     = false;
    
    ///////////////////////////////////////////////////////////////////////////
    
    /**
     * RemoteImagesLoader constructor.
     *
     * @author  myxobek
     *
     * @param   array   $data
     */
    public function __construct( $data )
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
        
        $error_code = $this->checkPackageRequirements();
        
        if ( $error_code !== self::ERROR_CODE_OK )
        {
            $common_error_message = 'Couldn\'t properly initialize package. Have a look at response below for more information:';
            
            $error_message = $this->getErrorText( $error_code );
            
            die(
                implode('',
                    [
                        $common_error_message,
                        "\n",
                        $error_message
                    ])
            );
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
    
        $this->extensions   = ['jpg', 'png', 'gif'];
    
        $this->destination  = './';
    
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
     * @return      int
     */
    public function checkPackageRequirements()
    {
        $this->connection = ftp_connect( $this->host );
        
        // create connection
        if ( $this->connection === false )
        {
            return self::ERROR_CODE_UNKNOWN_HOST;
        }
        
        // try to login
        if ( ! $login_result = @ftp_login( $this->connection, $this->login, $this->password ) )
        {
            return self::ERROR_CODE_CANNOT_LOGIN;
        }
    
        // set passive mode
        if ( ftp_pasv( $this->connection, true ) === false )
        {
            return self::ERROR_CODE_CANNOT_SET_PASSIVE_MODE;
        }
        
        return self::ERROR_CODE_OK;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    
    public function parse()
    {
        
    }
    
    ///////////////////////////////////////////////////////////////////////////
}


