<?php

/*
 * This file is part of the EverzetBehatBundle.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$steps->Then('/^Response status code is (\d+)$/', function($world, $code) {
    assertEquals($code, $world->client->getResponse()->getStatusCode());
});

$steps->Then('/^I should see "([^"]*)"$/', function($world, $text) {
    assertRegExp('/' . $text . '/', $world->client->getResponse()->getContent());
});

$steps->Then('/^I should not see "([^"]*)"$/', function($world, $text) {
    assertNotRegExp('/' . $text . '/', $world->client->getResponse()->getContent());
});

$steps->Then('/^I should see element "([^"]*)"$/', function($world, $css) {
    assertTrue(count($world->crawler->filter($css)) > 0);
});

$steps->Then('/^Header "([^"]*)" is set to "([^"]*)"$/', function($world, $key, $value) {
    assertTrue($world->client->getResponse()->headers->has($key));
    assertEquals($value, $world->client->getResponse()->headers->get($key));
});

$steps->Then('/^Header "([^"]*)" is not set to "([^"]*)"$/', function($world, $key, $value) {
    assertTrue($world->client->getResponse()->headers->has($key));
    assertNotEquals($value, $world->client->getResponse()->headers->get($key));
});

$steps->Then('/^I was redirected$/', function($world) {
    assertTrue($world->client->getResponse()->isRedirection());
});

$steps->Then('/^I was not redirected$/', function($world) {
    assertFalse($world->client->getResponse()->isRedirection());
});

$steps->Then('/^Print output$/', function($world) {
    $world->printDebug($world->client->getResponse()->getContent());
});
