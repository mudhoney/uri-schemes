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
 * Immutable Value object representing a Ws(s) Uri.
 *
 * @package    League\Uri
 * @subpackage League\Uri\Schemes
 * @author     Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since      1.0.0
 */
class Ws extends AbstractUri implements Uri
{
    /**
     * Supported schemes and corresponding default port
     *
     * @var array
     */
    protected static $supported_schemes = [
        'ws' => 80,
        'wss' => 443,
    ];

    /**
     * Tell whether the Ws(s) URI is in valid state.
     *
     * @return bool
     */
    protected function isValidUri()
    {
        return null === $this->fragment
            && '' !== $this->host
            && (null === $this->scheme || isset(static::$supported_schemes[$this->scheme]))
            && !('' != $this->scheme && null === $this->host);
    }
}
