<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Session\Handler;

class Redis implements \SessionHandlerInterface {

	/** @var \Redis */
	private $instance;

	public function __construct() {
		$this->instance = new \Redis();
		$this->instance->connect('localhost', 6379);
	}

	public function close(): bool {
		return true;
	}

	public function destroy($session_id) {
		return $this->instance->delete($session_id);
	}

	public function gc($maxlifetime) {
		return true;
	}

	public function open($save_path, $name) {
		return true;
	}

	public function read($session_id) {
		\OC::$server->getLogger()->critical('READING');
		\OC::$server->getLogger()->critical('sessid: ' . $session_id);

		$session_data = $this->instance->get($session_id);

		if ($session_data === false) {
			$session_data = '';
		}

		\OC::$server->getLogger()->critical('data: ' . $session_data);
		return $session_data;
	}

	public function write($session_id, $session_data) {
		\OC::$server->getLogger()->critical('WRITING');
		\OC::$server->getLogger()->critical($session_id);
		\OC::$server->getLogger()->critical($session_data);
		return $this->instance->set($session_id, $session_data);
	}

}
