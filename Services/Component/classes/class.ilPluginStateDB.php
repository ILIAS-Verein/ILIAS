<?php declare(strict_types=1);

use ILIAS\Data\Version;

/**
 * Repository interface for plugin state data.
 */
interface ilPluginStateDB
{
    public function isPluginActivated(string $id) : bool;
    public function getCurrentPluginVersion(string $id) : ?Version;
    public function getCurrentPluginDBVersion(string $id) : ?int;
}
