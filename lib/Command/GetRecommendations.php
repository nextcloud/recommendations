<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Command;

use OCA\Recommendations\Service\RecommendationService;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetRecommendations extends Command {
	private IUserManager $userManager;
	private RecommendationService $recommendationService;

	public function __construct(IUserManager $userManager,
		RecommendationService $recommendationService) {
		parent::__construct();

		$this->userManager = $userManager;
		$this->recommendationService = $recommendationService;
	}

	protected function configure() {
		$this->setName('files:recommendations:recommend');
		$this->setDescription('Shows recommended files for an account');
		$this->addArgument(
			'uid',
			InputArgument::REQUIRED,
			'user id'
		);
		$this->addArgument(
			'max',
			InputArgument::OPTIONAL,
			'maximum results'
		);
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$user = $this->userManager->get(
			$input->getArgument('uid')
		);

		if (is_null($user)) {
			$output->writeln("user does not exist");
			return 1;
		}

		if ($input->getArgument('max')) {
			$recommendations = $this->recommendationService->getRecommendations($user, (int) $input->getArgument('max'));
		} else {
			$recommendations = $this->recommendationService->getRecommendations($user);
		}
		foreach ($recommendations as $recommendation) {
			$reason = $recommendation->getReason();
			$path = $recommendation->getNode()->getPath();
			$ts = $recommendation->getTimestamp();
			$output->writeln("$reason: $path ($ts)");
		}

		return 0;
	}
}
