<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * Every time a user opened a file, as clients report it
 */
class OpenedFileMapper {
	public const TABLE_NAME = 'recommendations_opened';

	public function __construct(
		private readonly IDBConnection $db,
	) {
	}

	public function insert(string $userId, int $fileId, int $openedAt): void {
		$qb = $this->db->getQueryBuilder();
		$qb->insert(self::TABLE_NAME)
			->values([
				'user_id' => $qb->createNamedParameter($userId),
				'file_id' => $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT),
				'opened_at' => $qb->createNamedParameter($openedAt, IQueryBuilder::PARAM_INT),
			])
			->executeStatement();
	}

	/**
	 * The files a user opened, the most recently opened first, once each
	 *
	 * @return list<array{fileId: int, openedAt: int}>
	 */
	public function findRecent(string $userId, int $limit, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('file_id')
			->selectAlias($qb->func()->max('opened_at'), 'last_opened')
			->from(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->groupBy('file_id')
			->orderBy('last_opened', 'DESC')
			->addOrderBy('file_id', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		$result = $qb->executeQuery();
		$recent = [];
		while ($row = $result->fetchAssociative()) {
			$recent[] = [
				'fileId' => (int)$row['file_id'],
				'openedAt' => (int)$row['last_opened'],
			];
		}
		$result->closeCursor();

		return $recent;
	}

	public function deleteOlderThan(int $timestamp): int {
		$qb = $this->db->getQueryBuilder();
		return $qb->delete(self::TABLE_NAME)
			->where($qb->expr()->lt('opened_at', $qb->createNamedParameter($timestamp, IQueryBuilder::PARAM_INT)))
			->executeStatement();
	}

	public function deleteByUser(string $userId): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::TABLE_NAME)
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->executeStatement();
	}
}
