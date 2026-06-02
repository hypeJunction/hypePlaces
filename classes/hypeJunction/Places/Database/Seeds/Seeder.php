<?php

declare(strict_types=1);

namespace hypeJunction\Places\Database\Seeds;

use Elgg\Database\Seeds\Seed;
use Elgg\Event;

/**
 * Seed / unseed hypeplaces plugin entities.
 *
 * Owned entity types: 'hjplace'
 *
 * Register via:
 *   elgg_register_event_handler('seeds', 'database', [Seeder::class, 'addSeed']);
 *
 * Run with:
 *   php elgg-cli database:seed --type=hypeplaces
 *   php elgg-cli database:unseed --type=hypeplaces
 */
final class Seeder extends Seed
{
    /**
     * Identifies this seeder to the CLI: --type=hypeplaces
     */
    public static function getType(): string
    {
        return 'hypeplaces';
    }

    /**
     * Options passed to elgg_get_entities() when counting existing seeds.
     *
     * @return array<string, mixed>
     */
    protected function getCountOptions(): array
    {
        return [
            'type' => 'object',
            'metadata_name_value_pairs' => [
                ['name' => '__faker', 'value' => true],
            ],
        ];
    }

    /**
     * Create one seeded entity of each owned type.
     */
    public function seed(): void
    {
        // object/hjplace
        $entity = $this->createObject([
            'subtype' => 'hjplace',
        ]);
        $entity->title = $this->faker->sentence(3);
        $entity->description = $this->faker->paragraph();
        $entity->__faker = true;
        $entity->save();
    }

    /**
     * Delete all entities previously created by this seeder (tagged __faker=true).
     */
    public function unseed(): void
    {
        // Unseed object/hjplace
        $entities = elgg_get_entities([
            'type' => 'object',
            'subtype' => 'hjplace',
            'metadata_name_value_pairs' => [['name' => '__faker', 'value' => true]],
            'limit' => false,
        ]);
        foreach ($entities as $e) {
            $e->delete();
        }
    }

    /**
     * Event handler for 'seeds', 'database' — appends this class to the seeds list.
     */
    public static function addSeed(Event $event): array
    {
        $value = $event->getValue() ?? [];
        $value[] = __CLASS__;
        return $value;
    }
}