<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * Minimal OC\Hooks stubs required by OCP interfaces (e.g. IRootFolder) when
 * running unit tests without a Nextcloud server installation.
 */
namespace OC\Hooks {
	interface Emitter {
		public function listen($scope, $method, callable $callback);
		public function removeListener($scope = null, $method = null, ?callable $callback = null);
	}

	trait EmitterTrait {
		public function listen($scope, $method, callable $callback) {
		}
		public function removeListener($scope = null, $method = null, ?callable $callback = null) {
		}
	}

	abstract class BasicEmitter implements Emitter {
		use EmitterTrait;
	}

	class PublicEmitter extends BasicEmitter {
		public function emit($scope, $method, array $arguments = []) {
		}
	}
}
