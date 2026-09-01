<?php
/**
 * Singleton trait.
 * 
 * I know singletons are "considered harmful" but for things like
 * the ViewEngine that genuinely should only exist once, this is fine.
 * I use it sparingly.
 */
namespace App\Core;

trait Singleton
{
    private static ?self $instance = null;

    public static function getInstance(): static
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    // prevent cloning
    private function __clone() {}
}
