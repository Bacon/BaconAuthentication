<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use Zend\EventManager\EventManagerInterface;

interface EventAwarePluginInterface
{
    /**
     * Attaches the plugin to one or more events.
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attachToEvents(EventManagerInterface $events);
}
