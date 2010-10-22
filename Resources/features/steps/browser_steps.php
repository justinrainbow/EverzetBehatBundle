<?php

/*
 * This file is part of the EverzetBehatBundle.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$steps->Given('/^I am on(?: the)? (.*)$/', function($world, $page) {
    $world->crawler = $world->client->request('GET', $world->pathTo($page));
});

$steps->When('/^I go to(?: the)? (.*)$/', function($world, $page) {
    $world->crawler = $world->client->request('GET', $world->pathTo($page));
});

$steps->When('/^I (?:follow|click)(?: the)? "([^"]*)"(?: link)*$/', function($world, $link) {
    $link = $world->crawler->selectLink($link)->link();
    $world->crawler = $world->client->click($link);
});

$steps->When('/^I go back$/', function($world) {
    $world->client->back();
});

$steps->When('/^I go forward$/', function($world) {
    $world->client->forward();
});

$steps->When('/^I send (POST|PUT|DELETE) to (.*) with:$/', function($world, $method, $page, $table) {
    $world->crawler = $world->client->request($method, $world->pathTo($page), current($table->getHash()));
});

$steps->When('/^I follow redirect$/', function($world) {
    $world->crawler = $world->client->followRedirect();
});
