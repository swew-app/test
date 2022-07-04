<?php

declare(strict_types=1);

namespace SWEW\Test\Suite;

enum SuiteHook: string
{
    case BeforeAll = 'beforeAll';
    case BeforeEach = 'beforeEach';
    case AfterEach = 'afterEach';
    case AfterAll = 'afterAll';
}
