<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\Attributes\CreateTable;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

#[CreateTable(
	table: self::TABLE_NAME,
	columns: ['id', 'user_id', 'file_id'],
	description: 'Files users opened, to recommend the ones they work with',
)]
class Version9000Date20261029000000 extends SimpleMigrationStep {
	private const TABLE_NAME = 'recommendations_opened';

	/**
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 */
	#[\Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		$schema = $schemaClosure();

		if ($schema->hasTable(self::TABLE_NAME)) {
			return null;
		}

		$table = $schema->createTable(self::TABLE_NAME);
		$table->addColumn('id', Types::BIGINT, [
			'notnull' => true,
			'length' => 20,
			'unsigned' => true,
		]);
		$table->addColumn('user_id', Types::STRING, [
			'notnull' => true,
			'length' => 64,
		]);
		$table->addColumn('file_id', Types::BIGINT, [
			'notnull' => true,
			'length' => 20,
			'unsigned' => true,
		]);
		$table->setPrimaryKey(['id']);
		$table->addIndex(['user_id', 'file_id'], 'rec_opened_user_file_idx');

		return $schema;
	}
}
