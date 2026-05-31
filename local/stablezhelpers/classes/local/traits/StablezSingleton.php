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
 *
 * @package   local_stablezhelpers
 * @copyright 2025 stablez
 * @author    santoshtmp7
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_stablezhelpers\local\traits;

defined('MOODLE_INTERNAL') || die;

/**
 * Singleton Trait
 *
 * Provides singleton pattern implementation with:
 * - Single instance per class
 * - Protected constructor (calls init() hook)
 * - Prevention of cloning and unserialization
 *
 * @since 1.0.0
 *
 * @method static get_instance() Get singleton instance
 * @method void init()           Initialize the class (override in child)
 */
trait StablezSingleton {

    /**
     * Single instance of the class
     *
     * @since 1.0.0
     * @var static
     */
    protected static $instance = null;

    /**
     * Get instance of the class
     *
     * Uses `static` return type for late static binding,
     * ensuring the correct class instance is returned.
     *
     * @since 1.0.0
     *
     * @return static The singleton instance
     */
    public static function get_instance(): static {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Prevent cloning of the instance
     *
     * @since 1.0.0
     * @return void
     */
    final protected function __clone() {
    }

    /**
     * Prevent unserializing of the instance
     *
     * @since 1.0.0
     * @return void
     * @throws \Exception When attempting to unserialize the singleton
     */
    final public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * Constructor (protected to prevent direct instantiation)
     *
     * @since 1.0.0
     */
    protected function __construct() {
        $this->init();
    }

    /**
     * Initialize the class
     *
     * Override this method in child classes to add initialization logic.
     *
     * @since 1.0.0
     * @return void
     */
    protected function init(): void {
        // Override in child class
    }
}
