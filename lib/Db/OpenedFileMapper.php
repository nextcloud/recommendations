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
	 * The files a user opened, the most often opened first, once each
	 *
	 * @return list<array{fileId: int, lastId: string}> with the id of the last time each was opened
	 */
	public function findFrequent(string $userId, int $limit, int $offset = 0): array {
		$qb = $this->connection->getQueryBuilder();
		$qb->select('file_id')
			->selectAlias($qb->func()->max('id'), 'last_id')
			->selectAlias($qb->func()->count('id'), 'opens')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->groupBy('file_id')
			->orderBy('opens', 'DESC')
			->addOrderBy('last_id', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		$result = $qb->executeQuery();
		$frequent = [];
		while ($row = $result->fetchAssociative()) {
			$frequent[] = [
				'fileId' => (int)$row['file_id'],
				'lastId' => (string)$row['last_id'],
			];
		}
		$result->closeCursor();

		return $frequent;
	}

	/**
	 * How many times a user opened each of these files
	 *
	 * @param list<int> $fileIds
	 * @return array<int, int> opens by file id, only for files opened at all
	 */
	public function countOpens(string $userId, array $fileIds): array {
		$counts = [];
		foreach (array_chunk($fileIds, IQueryBuilder::MAX_IN_PARAMETERS) as $chunk) {
			$qb = $this->connection->getQueryBuilder();
			$qb->select('file_id')
				->selectAlias($qb->func()->count('id'), 'opens')
				->from($this->getTableName())
				->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
				->andWhere($qb->expr()->in('file_id', $qb->createNamedParameter($chunk, IQueryBuilder::PARAM_INT_ARRAY)))
				->groupBy('file_id');

			$result = $qb->executeQuery();
			while ($row = $result->fetchAssociative()) {
				$counts[(int)$row['file_id']] = (int)$row['opens'];
			}
			$result->closeCursor();
		}

		return $counts;
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
