<?php


/**
 * Feed
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

class Feed
{
    /**
     * @static
     * @param $feed
     * @param int $limit
     * @return array
     */
    public static function parse($feed, $limit = 0)
    {
        if (strpos($feed, '<?') === false) {
            $feed = @simplexml_load_file($feed, 'SimpleXMLElement', LIBXML_NOCDATA);
        } else {
            $feed = @simplexml_load_string($feed, 'SimpleXMLElement', LIBXML_NOCDATA);
        }

        if ($feed === false)
            return array();

        $namespaces = $feed->getNamespaces(true);

        // Detect the feed type. RSS 1.0/2.0 and Atom 1.0 are supported.
        $feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;

        $feed_items = array();

        foreach ($feed as $i => $item) {
            if ($limit && $i === $limit)
                break;

            $data = (array)$item;

            // get namespace
            foreach ($namespaces as $ns)
                $data += (array)$item->children($ns);

            $feed_items[] = $data;
        }

        return $feed_items;
    }
}