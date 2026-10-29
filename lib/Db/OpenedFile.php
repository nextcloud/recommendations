<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Db;

use OCP\AppFramework\ORM\Attribute\Column;
use OCP\AppFramework\ORM\Attribute\Entity;
use OCP\AppFramework\ORM\Attribute\Id;
use OCP\DB\Schema\ColumnType;
use OCP\Snowflake\ISnowflakeGenerator;

/**
 * A user opening a file, as a client reported it. The snowflake id holds
 * when it was opened.
 *
 * @psalm-suppress MissingConstructor ORM based hydration
 */
#[Entity(name: 'recommendations_opened')]
final class OpenedFile {
	#[Id(generatorClass: ISnowflakeGenerator::class)]
	#[Column(name: 'id', type: ColumnType::Bigint)]
	public ?string $id = null;

	#[Column(name: 'user_id', type: ColumnType::String, length: 64)]
	public string $userId;

	#[Column(name: 'file_id', type: ColumnType::Bigint)]
	public int $fileId;
}
