<?php

declare(strict_types=1);

namespace hypeJunction\Places;

use Elgg\Database\Seeds\Seed;

class Seeder extends Seed {

	public function getType(): string {
		return 'hypeplaces';
	}

	public function seed(): void {
		$this->advance('Seeding hypeplaces entities...');

		$count = $this->getCount();

		for ($i = 0; $i < $count; $i++) {
			$user = $this->getRandomUser();
			if (!$user) {
				continue;
			}

			// TODO: Create a Place entity with faker data
			// $entity = new \hypeJunction\Places\Place();
			// $entity->owner_guid = $user->guid;
			// $entity->container_guid = $user->guid;
			// $entity->title = $this->faker()->company();
			// $entity->description = $this->faker()->paragraph();
			// $entity->save();

			// Tag for cleanup:
			// $entity->setMetadata('__faker', true);

			$this->advance();
		}
	}

	public function unseed(): void {
		$batch = elgg_get_entities([
			'type' => 'object',
			'subtype' => 'hjplace',
			'metadata_name' => '__faker',
			'metadata_value' => true,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
		]);

		foreach ($batch as $entity) {
			$entity->delete();
			$this->advance();
		}
	}

	public static function addSeed(\Elgg\Event $event): mixed {
		$seeds = $event->getValue();
		$seeds[] = static::class;
		return $seeds;
	}

	public function getCountOptions(): array {
		return [
			'type' => 'object',
			'subtype' => 'hjplace',
		];
	}
}
