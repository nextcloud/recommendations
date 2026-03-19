<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Hooks {
	interface Emitter {
		public function listen($scope, $method, callable $callback);
		public function removeListener($scope = null, $method = null, ?callable $callback = null);
	}

	trait EmitterTrait {
		public function listen($scope, $method, callable $callback) {}
		public function removeListener($scope = null, $method = null, ?callable $callback = null) {}
	}

	abstract class BasicEmitter implements Emitter {
		use EmitterTrait;
	}

	class PublicEmitter extends BasicEmitter {
		public function emit($scope, $method, array $arguments = []) {}
	}
}

namespace {
	require_once __DIR__ . '/../vendor-bin/phpunit/vendor/autoload.php';
	require_once __DIR__ . '/../vendor/autoload.php';
}
