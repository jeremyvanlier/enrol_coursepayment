<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * arraysortutil is a array sort utility, you can extends the sorting engine.
 *
 * Used for sorting of already requested parsed data and that is not available in mysql
 *
 * @version 0.1
 * @author  coderkk Cudnik <coderkk@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @package utility.sort
 */

namespace enrol_coursepayment\table;
defined('MOODLE_INTERNAL') || die;

/**
 * Class arraysortutil
 *
 * @package enrol_coursepayment\table
 */
class arraysortutil {
    static public function uasort($unsort, $fields) {
        if (!is_array($unsort) || count($unsort) <= 0) {
            return $unsort;
        }
        $sorted = uasortengine::uasort($unsort, $fields);

        return $sorted;
    }

    static public function multisort($unsort, $fields) {
        if (!is_array($unsort) || count($unsort) <= 0) {
            return $unsort;
        }
        $sorted = multisortengine::multisort($unsort, $fields);

        return $sorted;
    }
}

/**
 * Class multisortengine
 *
 * @package enrol_coursepayment\table
 */
class multisortengine {
    static public function multisort($unsort, $fields) {
        $sorted = $unsort;
        if (is_array($unsort)) {
            $loadfields = [];
            foreach ($fields as $sortfield) {
                $loadfields["field"][] = [
                    "name" => $sortfield["field"],
                    "order" => $sortfield["order"],
                    "nature" => (isset($sortfield["nature"]) ? $sortfield["nature"] : false),
                    "caseensitve" => (isset($sortfield["caseensitve"]) ? $sortfield["caseensitve"] : false),
                ];
                $loadfields["data"][$sortfield["field"]] = [];
            }
            // Obtain a list of columns.
            foreach ($sorted as $key => $row) {
                foreach ($loadfields["field"] as $field) {
                    $value = $row->{$field["name"]};
                    $loadfields["data"][$field["name"]][$key] = $value;
                }
            }
            $parameters = [];
            foreach ($loadfields["field"] as $sortfield) {
                $arraydata = $loadfields["data"][$sortfield["name"]];
                $caseensitve = ($sortfield["caseensitve"] == null) ? $sortfield["caseensitve"] : false;
                if (!$caseensitve) {
                    $arraydata = array_map('strtolower', $arraydata);
                }
                $parameters[] = $arraydata;
                if ($sortfield["order"] != null) {
                    $parameters[] = ($sortfield["order"]) ? SORT_DESC : SORT_ASC;
                }
                if ($sortfield["nature"] != null) {
                    $parameters[] = ($sortfield["nature"]) ? SORT_REGULAR : SORT_STRING;
                }
            }
            $parameters[] = &$sorted;
            call_user_func_array("array_multisort", $parameters);
        }

        return $sorted;
    }
}

/**
 * Class uasortengine
 *
 * @package enrol_coursepayment\table
 */
class uasortengine {
    static private $caseensitve = false;
    static private $sortfields = [];
    static private $sortorder = true;
    static private $nature = false;

    /**
     * uasort_callback
     *
     * @param $a
     * @param $b
     *
     * @return int|\lt
     */
    static private function uasort_callback(&$a, &$b) {
        foreach (self::$sortfields as $sortfield) {
            $field = $sortfield["field"];
            $order = isset($sortfield["order"]) ? $sortfield["order"] : self::$sortorder;
            $caseensitve = isset($sortfield["caseensitve"]) ? $sortfield["caseensitve"] : self::$caseensitve;
            $nature = isset($sortfield["nature"]) ? $sortfield["nature"] : self::$nature;
            if ($field != "") {
                $retval = 0;
                if ($nature) {
                    if ($caseensitve) {
                        $compare = strnatcmp($a[$field], $b[$field]);
                    } else {
                        $compare = strnatcasecmp($a[$field], $b[$field]);
                    }
                } else {
                    if ($caseensitve) {
                        $compare = strcmp($a[$field], $b[$field]);
                    } else {
                        $compare = strcasecmp($a[$field], $b[$field]);
                    }
                }
                if ($compare !== 0 && !$order) {
                    $compare = ($compare > 0) ? -1 : 1;
                }
            }
            if ($compare !== 0) {
                break;
            }
        }

        return $compare;
    }

    /**
     * uasort
     *
     * @param $unsort
     * @param $fields
     *
     * @return mixed
     */
    static public function uasort($unsort, $fields) {
        self::$sortfields = $fields;
        $sorted = $unsort;
        uasort($sorted, ['uasortengine', 'uasort_callback']);

        return $sorted;
    }
}