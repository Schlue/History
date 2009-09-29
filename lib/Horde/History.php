<?php
/**
 * The Horde_History:: system.
 *
 * PHP version 5
 *
 * @category Horde
 * @package  History
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=History
 */

/**
 * The Autoloader allows us to omit "require/include" statements.
 */
require_once 'Horde/Autoloader.php';

/**
 * The Horde_History:: class provides a method of tracking changes in Horde
 * objects, stored in a SQL table.
 *
 * Copyright 2003-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Horde
 * @package  History
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=History
 */
class Horde_History
{
    /**
     * Instance cache.
     *
     * @var array
     */
    static protected $_instances;

    /**
     * Attempts to return a reference to a concrete History instance.
     * It will only create a new instance if no History instance
     * currently exists.
     *
     * This method must be invoked as: $var = History::singleton()
     *
     * @param string $driver The driver to use.
     *
     * @return Horde_History  The concrete Horde_History reference.
     * @throws Horde_Exception
     */
    static public function singleton($driver = null)
    {
        global $conf;

        if (empty($driver)) {
            $driver = 'Sql';
        }

        if ($driver == 'Sql') {
            if (empty($conf['sql']['phptype'])
                || ($conf['sql']['phptype'] == 'none')) {
                throw new Horde_Exception(_("The History system is disabled."));
            }
            $params = $conf['sql'];
        } else {
            $params = array();
        }

        if (!isset(self::$_instances[$driver])) {
            $injector = new Horde_Injector(new Horde_Injector_TopLevel());
            $injector->bindFactory(
                'Horde_History',
                'Horde_History_Factory',
                'getHistory'
            );
            $config = new stdClass;
            $config->driver = $driver;
            $config->params = $params;
            $injector->setInstance('Horde_History_Config', $config);
            self::$_instances[$driver] = $injector->getInstance('Horde_History');
        }

        return self::$_instances[$driver];
    }

    /**
     * Logs an event to an item's history log. The item must be uniquely
     * identified by $guid. Any other details about the event are passed in
     * $attributes. Standard suggested attributes are:
     *
     *   'who' => The id of the user that performed the action (will be added
     *            automatically if not present).
     *
     *   'ts' => Timestamp of the action (this will be added automatically if
     *           it is not present).
     *
     * @param string  $guid          The unique identifier of the entry to
     *                               add to.
     * @param array   $attributes    The hash of name => value entries that
     *                               describe this event.
     * @param boolean $replaceAction If $attributes['action'] is already
     *                               present in the item's history log,
     *                               update that entry instead of creating a
     *                               new one.
     *
     * @return boolean True if the operation succeeded.
     *
     * @throws Horde_Exception
     */
    public function log($guid, array $attributes = array(),
                        $replaceAction = false)
    {
        $history = $this->getHistory($guid);

        if (!isset($attributes['who'])) {
            $attributes['who'] = Horde_Auth::getAuth();
        }
        if (!isset($attributes['ts'])) {
            $attributes['ts'] = time();
        }

        return $this->_log($history, $attributes, $replaceAction);
    }

    /**
     * Logs an event to an item's history log. Any other details about the event
     * are passed in $attributes.
     *
     * @param Horde_HistoryObject $history       The history item to add to.
     * @param array               $attributes    The hash of name => value entries
     *                                           that describe this event.
     * @param boolean             $replaceAction If $attributes['action'] is
     *                                           already present in the item's
     *                                           history log, update that entry
     *                                           instead of creating a new one.
     *
     * @return boolean True if the operation succeeded.
     *
     * @throws Horde_Exception
     */
    protected function _log(Horde_HistoryObject $history, array $attributes,
                            $replaceAction = false)
    {
        throw new Horde_Exception('Not implemented!');
    }

    /**
     * Returns a Horde_HistoryObject corresponding to the named history
     * entry, with the data retrieved appropriately.
     *
     * @param string $guid The name of the history entry to retrieve.
     *
     * @return Horde_HistoryObject A Horde_HistoryObject
     *
     * @throws Horde_Exception
     */
    public function getHistory($guid)
    {
        throw new Horde_Exception('Not implemented!');
    }

    /**
     * Finds history objects by timestamp, and optionally filter on other
     * fields as well.
     *
     * @param string  $cmp     The comparison operator (<, >, <=, >=, or =) to
     *                         check the timestamps with.
     * @param integer $ts      The timestamp to compare against.
     * @param array   $filters An array of additional (ANDed) criteria.
     *                         Each array value should be an array with 3
     *                         entries:
     * <pre>
     * 'field' - the history field being compared (i.e. 'action').
     * 'op'    - the operator to compare this field with.
     * 'value' - the value to check for (i.e. 'add').
     * </pre>
     * @param string  $parent  The parent history to start searching at. If
     *                         non-empty, will be searched for with a LIKE
     *                         '$parent:%' clause.
     *
     * @return array  An array of history object ids, or an empty array if
     *                none matched the criteria.
     *
     * @throws Horde_Exception
     */
    public function getByTimestamp($cmp, $ts, array $filters = array(),
                                   $parent = null)
    {
        throw new Horde_Exception('Not implemented!');
    }

    /**
     * Gets the timestamp of the most recent change to $guid.
     *
     * @param string $guid   The name of the history entry to retrieve.
     * @param string $action An action: 'add', 'modify', 'delete', etc.
     *
     * @return integer  The timestamp, or 0 if no matching entry is found.
     *
     * @throws Horde_Exception
     */
    public function getActionTimestamp($guid, $action)
    {
        /* This implementation still works, but we should be able to
         * get much faster now with a SELECT MAX(history_ts)
         * ... query. */
        try {
            $history = $this->getHistory($guid);
        } catch (Horde_Exception $e) {
            return 0;
        }

        $last = 0;

        if (is_array($history->data)) {
            foreach ($history->data as $entry) {
                if (($entry['action'] == $action) && ($entry['ts'] > $last)) {
                    $last = $entry['ts'];
                }
            }
        }

        return (int)$last;
    }

    /**
     * Remove one or more history entries by name.
     *
     * @param array $names The history entries to remove.
     *
     * @return boolean True if the operation succeeded.
     *
     * @throws Horde_Exception
     */
    public function removeByNames(array $names)
    {
        throw new Horde_Exception('Not implemented!');
    }

}
