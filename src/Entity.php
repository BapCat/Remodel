<?php declare(strict_types=1); namespace BapCat\Remodel;

/**
 * An Entity
 */
interface Entity {
  public function cacheRelations(): void;
}
