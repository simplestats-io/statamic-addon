<?php

namespace SimpleStatsIo\StatamicAddon\Tests;

use SimpleStatsIo\StatamicAddon\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
