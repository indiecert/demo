<?php

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Tpl\Twig\TwigTemplateManager;
use fkooman\Rest\Plugin\Authentication\AuthenticationPlugin;
use fkooman\Rest\Plugin\Authentication\IndieAuth\IndieAuthAuthentication;
use GuzzleHttp\Client;
use fkooman\Http\Session;
use fkooman\Http\Exception\InternalServerErrorException;
use fkooman\Config\Reader;
use fkooman\Config\YamlFile;
use fkooman\IndieCert\Demo\DemoService;

try {
    $reader = new Reader(
        new YamlFile(dirname(__DIR__).'/config/config.yaml')
    );

    $serverMode = $reader->v('serverMode', false, 'production');

    // TemplateManager
    $templateManager = new TwigTemplateManager(
        array(
            dirname(__DIR__).'/views',
            dirname(__DIR__).'/config/views',
        ),
        $reader->v('templateCache', false, null)
    );

    // Guzzle
    $client = new Client(
        array(
            'defaults' => array(
                'verify' => 'development' !== $serverMode,
                'timeout' => 10,
            ),
        )
    );

    // Session
    $session = new Session(
        'indiecert-demo',
        array(
            'secure' => 'development' !== $serverMode,
        )
    );

    $service = new DemoService($templateManager);
    $authenticationPlugin = new AuthenticationPlugin();
    $indieAuth = new IndieAuthAuthentication($templateManager, $client, $session);

    $authenticationEndpoint = $reader->v('authenticationEndpoint', false);
    // if authentication endpoint is set, use it instead of discovery
    if (!is_null($authenticationEndpoint)) {
        $indieAuth->setAuthUri($authenticationEndpoint);
    }
    $authenticationPlugin->register($indieAuth, 'indieauth');
    $service->getPluginRegistry()->registerDefaultPlugin($authenticationPlugin);
    $service->run()->send();
} catch (Exception $e) {
    // internal server error
    error_log($e->__toString());
    $e = new InternalServerErrorException($e->getMessage());
    $e->getHtmlResponse()->send();
}
