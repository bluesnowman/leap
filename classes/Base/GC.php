<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This class manages garbage collection.
 *
 * @package Leap
 * @category System
 * @version 2013-02-05
 *
 * @see http://msdn.microsoft.com/en-us/library/system.gc.aspx
 *
 * @abstract
 */
abstract class Base_GC extends Core_Object {

	/**
	 * This function forces garbage collector to start immediately.
	 *
	 * @access public
	 * @static
	 *
	 * @see http://www.php.net/manual/en/features.gc.php
	 * @see http://www.php.net/manual/en/features.gc.refcounting-basics.php
	 * @see http://www.php.net/manual/en/features.gc.collecting-cycles.php
	 * @see http://www.php.net/manual/en/function.gc-collect-cycles.php
	 */
	public static function run() {
		if (function_exists('gc_collect_cycles')) {
			gc_enable();
			if (gc_enabled()) {
				gc_collect_cycles();
			}
			gc_disable();
		}
	}

}
