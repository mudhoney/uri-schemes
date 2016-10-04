<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package    League\Uri
 * @subpackage League\Uri\Schemes
 * @author     Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright  2016 Ignace Nyamagana Butera
 * @license    https://github.com/thephpleague/uri-components/blob/master/LICENSE (MIT License)
 * @version    1.0.0
 * @link       https://github.com/thephpleague/uri-components
 */
namespace League\Uri\Schemes;

use League\Uri\Interfaces\Uri;

/**
 * Immutable Value object representing a File Uri.
 *
 * @package    League\Uri
 * @subpackage League\Uri\Schemes
 * @author     Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since      1.0.0
 */
class File extends AbstractUri implements Uri
{
    /**
     * @inheritdoc
     */
    protected static $supported_schemes = [
        'file' => null,
    ];

    /**
     * Tell whether the File URI is in valid state.
     *
     * @return bool
     */
    protected function isValidUri()
    {
        $filter =  function ($value) {
            return $value !== null;
        };

        $res = array_filter([
            $this->user_info, $this->port,
            $this->query, $this->fragment,
        ], $filter);

        return empty($res) && $this->isValidGenericUri() && $this->isAllowedAuthority();
    }

    /**
     * Tell whether the current Authority is valid
     *
     * @return bool
     */
    protected function isAllowedAuthority()
    {
        return !('' !== $this->getScheme() && null === $this->host);
    }


    /**
     * Create a new instance from a Unix path string
     *
     * @param string $uri
     *
     * @return static
     */
    public static function createFromUnixPath($uri = '')
    {
        $uri = implode('/', array_map('rawurlencode', explode('/', $uri)));
        if (isset($uri) && '/' === $uri[0]) {
            $uri = 'file://'.$uri;
        }

        return new static($uri);
    }

    /**
     * Create a new instance from a local Windows path string
     *
     * @param string $uri
     *
     * @return static
     */
    public static function createFromWindowsPath($uri = '')
    {
        $root = '';
        if (preg_match(',^(?<root>[a-zA-Z][:|\|]),', $uri, $matches)) {
            $root = substr($matches['root'], 0, -1).':';
            $uri = substr($uri, strlen($root));
        }

        $uri = implode('/', array_map('rawurlencode', explode('\\', $uri)));

        //Local Windows absolute path
        if ('' !== $root) {
            $uri = 'file:///'.$root.$uri;
        }

        //UNC Windows Path
        if ('//' === substr($uri, 0, 2)) {
            $uri = 'file:'.$uri;
        }

        return new static($uri);
    }
}