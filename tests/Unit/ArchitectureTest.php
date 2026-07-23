<?php

arch('domain code does not depend on the HTTP layer')
    ->expect('App\Domain')
    ->not->toUse('App\Http');

arch('catalog does not depend on downstream application modules')
    ->expect('App\Domain\Catalog')
    ->not->toUse('App\Domain\Applications');

arch('controllers use the controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller');
