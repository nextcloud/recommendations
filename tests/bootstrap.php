<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

// When the app is installed inside a Nextcloud server (e.g. in CI) load the
// server base so that all OCP/OC classes are available even after
// `composer remove nextcloud/ocp --dev`.
$serverBase = __DIR__ . '/../../../lib/base.php';
if (file_exists($serverBase)) {
	require_once $serverBase;
}

// Standalone unit-test run: define minimal OC\Hooks stubs required by the
// OCP interfaces loaded via the nextcloud/ocp dev-dependency.
if (!interface_exists('OC\Hooks\Emitter', false)) {
	require_once __DIR__ . '/OcHooksStubs.php';
}

require_once __DIR__ . '/../vendor/autoload.php';
