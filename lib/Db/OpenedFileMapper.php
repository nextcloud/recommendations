<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Db;

use OCP\AppFramework\ORM\Repository;
use OCP\DB\QueryBuilder\IQueryBuilder;

/**
 * @template-extends Repository<OpenedFile>
 * @psalm-suppress ClassMustBeFinal For unit tests
 */
class OpenedFileMapper extends Repository {
	public const string entityClass = OpenedFile::class;

	public function add(string $userId, int $fileId): void {
		$openedFile = new OpenedFile();
		$openedFile->userId = $userId;
		$openedFile->fileId = $fileId;
		$this->insert($openedFile);
	}

	/**
	 * The files a user opened, the most recently opened first, once each
	 *
	 * @return list<array{fileId: int, lastId: string}> with the id of the last time each was opened
	 */
	public function findRecent(string $userId, int $limit, int $offset = 0): array {
		$qb = $this->connection->getQueryBuilder();
		$qb->select('file_id')
			->selectAlias($qb->func()->max('id'), 'last_id')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->groupBy('file_id')
			->orderBy('last_id', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		$result = $qb->executeQuery();
		$recent = [];
		while ($row = $result->fetchAssociative()) {
			$recent[] = [
				'fileId' => (int)$row['file_id'],
				'lastId' => (string)$row['last_id'],
			];
		}
		$result->closeCursor();

		return $recent;
	}

	/**
	 * Forget what was opened before this snowflake id
	 */
	public function deleteBefore(string $id): void {
		$qb = $this->connection->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where($qb->expr()->lt('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->executeStatement();
	}

	public function deleteByUser(string $userId): void {
		$this->deleteBy(['userId' => $userId]);
	}
}
