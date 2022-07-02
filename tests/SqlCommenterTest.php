<?php

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\SqlCommenter\Exceptions\InvalidSqlCommenter;
use Spatie\SqlCommenter\SqlCommenter;
use Spatie\SqlCommenter\Tests\TestSupport\TestClasses\CustomCommenter;
use Spatie\SqlCommenter\Tests\TestSupport\TestClasses\InvalidCustomCommenter;
use Spatie\SqlCommenter\Tests\TestSupport\TestClasses\UsersJob;

it('can add extra comments', function () {
    Event::listen(QueryExecuted::class, function (QueryExecuted $event) {
        expect($event->sql)->toContainComment('foo', 'bar');
    });

    SqlCommenter::addComment('foo', 'bar');

    dispatch(new UsersJob());
});

it('will not add comments if there already are comments', function () {
    Event::listen(QueryExecuted::class, function (QueryExecuted $event) {
        expect($event->sql)->not()->toContainComment('foo', 'bar');
    });

    SqlCommenter::addComment('foo', 'bar');

    DB::statement(<<<mysql
        select * from users; /*existing='comment'*/
    mysql);
});

it('can use a custom commenter class', function () {
    config()->set('sql-commenter.commenter_class', CustomCommenter::class);

    Event::listen(QueryExecuted::class, function (QueryExecuted $event) {
        expect($event->sql)->toContainComment('framework', 'spatie-framework');
    });

    dispatch(new UsersJob());
});

it('will throw an exception when trying to use an invalid commenter class', function () {
    config()->set('sql-commenter.commenter_class', InvalidCustomCommenter::class);

    dispatch(new UsersJob());
})->throws(InvalidSqlCommenter::class);
